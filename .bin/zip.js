const { createHash } = require( 'crypto' );
const fs = require( 'fs' );
const path = require( 'path' );
const { ZipArchive } = require( 'archiver' );

const root = path.resolve( __dirname, '..' );
const pkg = JSON.parse( fs.readFileSync( path.join( root, 'package.json' ), 'utf8' ) );
const deployDir = path.join( root, 'deploy' );
const packageDir = path.join( deployDir, pkg.name );
const zipName = `${ pkg.name }-${ pkg.version }.zip`;
const zipPath = path.join( deployDir, zipName );
const checksumPath = `${ zipPath }.sha256`;
const tempZipPath = path.join( root, `.${ zipName }.tmp` );
const fixedDate = new Date( '1980-01-01T00:00:00.000Z' );

function collectFiles( directory, prefix = '' ) {
	const entries = fs.readdirSync( directory, { withFileTypes: true } )
		.sort( ( left, right ) => left.name.localeCompare( right.name, 'en' ) );
	const files = [];

	for ( const entry of entries ) {
		const relative = prefix ? `${ prefix }/${ entry.name }` : entry.name;
		const absolute = path.join( directory, entry.name );

		if ( entry.isSymbolicLink() ) {
			throw new Error( `Symlinks are not allowed in the deployment package: ${ relative }` );
		}
		if ( entry.isDirectory() ) {
			files.push( ...collectFiles( absolute, relative ) );
		} else if ( entry.isFile() ) {
			files.push( { absolute, relative } );
		} else {
			throw new Error( `Unsupported file type in the deployment package: ${ relative }` );
		}
	}

	return files;
}

async function main() {
	if ( ! fs.existsSync( packageDir ) ) {
		throw new Error( 'Deployment package directory does not exist. Run the copy step first.' );
	}

	const files = collectFiles( packageDir ).sort( ( left, right ) =>
		left.relative < right.relative ? -1 : left.relative > right.relative ? 1 : 0
	);
	fs.rmSync( tempZipPath, { force: true } );

	const output = fs.createWriteStream( tempZipPath );
	const archive = new ZipArchive( { zlib: { level: 9 } } );
	const closed = new Promise( ( resolve, reject ) => {
		output.once( 'close', resolve );
		output.once( 'error', reject );
	} );
	let archiveError;

	archive.on( 'warning', ( error ) => {
		archiveError = error;
		archive.abort();
		output.destroy( error );
	} );
	archive.on( 'error', ( error ) => {
		archiveError = error;
		archive.abort();
		output.destroy( error );
	} );

	archive.pipe( output );
	for ( const file of files ) {
		archive.append( fs.readFileSync( file.absolute ), {
			name: `${ pkg.name }/${ file.relative }`,
			date: fixedDate,
			mode: 0o100644,
			stats: false,
		} );
	}

	try {
		await Promise.all( [ archive.finalize(), closed ] );
	} catch ( error ) {
		fs.rmSync( tempZipPath, { force: true } );
		throw archiveError || error;
	}

	fs.renameSync( tempZipPath, zipPath );
	const digest = createHash( 'sha256' ).update( fs.readFileSync( zipPath ) ).digest( 'hex' );
	fs.writeFileSync( checksumPath, `${ digest }  ${ zipName }\n` );
	const sizeKb = ( fs.statSync( zipPath ).size / 1024 ).toFixed( 1 );
	console.log( `ZIP created: deploy/${ zipName } (${ sizeKb } KB, SHA-256 ${ digest })` );
}

main().catch( ( error ) => {
	console.error( error.stack || error.message );
	process.exitCode = 1;
} );
