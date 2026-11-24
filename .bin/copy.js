const fs = require( 'fs' );
const path = require( 'path' );

const root = process.cwd();
const pkg = JSON.parse( fs.readFileSync( path.join( root, 'package.json' ), 'utf8' ) );
const destination = path.join( root, 'deploy' );
const packageDestination = path.join( destination, pkg.name );

const files = [ 'brand-master.php', 'readme.txt', 'changelog.txt', 'LICENSE', 'uninstall.php' ];
const directories = [ 'admin', 'assets', 'build', 'includes', 'languages', 'public' ];

for ( const file of files ) {
	if ( ! fs.existsSync( path.join( root, file ) ) ) {
		throw new Error( `Required package file is missing: ${ file }` );
	}
}

for ( const directory of directories ) {
	if ( ! fs.statSync( path.join( root, directory ) ).isDirectory() ) {
		throw new Error( `Required package directory is missing: ${ directory }` );
	}
}

fs.rmSync( destination, { recursive: true, force: true } );
fs.mkdirSync( packageDestination, { recursive: true } );

for ( const file of files ) {
	fs.copyFileSync( path.join( root, file ), path.join( packageDestination, file ) );
}

for ( const directory of directories ) {
	const source = path.join( root, directory );
	const target = path.join( packageDestination, directory );

	fs.cpSync( source, target, {
		recursive: true,
		dereference: false,
		filter: ( entry ) => {
			const basename = path.basename( entry );

			if ( basename.startsWith( '.' ) ) {
				return false;
			}

			if ( [ '.DS_Store', 'Thumbs.db', 'js.pot' ].includes( basename ) || basename.endsWith( '.map' ) ) {
				return false;
			}

			if ( fs.lstatSync( entry ).isSymbolicLink() ) {
				throw new Error( `Symlinks are not allowed in the deployment package: ${ entry }` );
			}

			return true;
		},
	} );
}

console.log( `Deployment package created: deploy/${ pkg.name }/` );
