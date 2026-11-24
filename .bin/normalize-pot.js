const { execFileSync } = require( 'child_process' );
const fs = require( 'fs' );
const path = require( 'path' );

const root = process.cwd();
const potPath = path.join( root, 'languages', 'brand-master.pot' );
const sourceDateEpoch = Number(
	process.env.SOURCE_DATE_EPOCH || execFileSync( 'git', [ 'show', '-s', '--format=%ct', 'HEAD' ], { encoding: 'utf8' } ).trim()
);

if ( ! Number.isSafeInteger( sourceDateEpoch ) || sourceDateEpoch < 0 ) {
	throw new Error( 'SOURCE_DATE_EPOCH must resolve to a non-negative Unix timestamp.' );
}

const creationDate = `${ new Date( sourceDateEpoch * 1000 ).toISOString().slice( 0, 19 ) }+00:00`;
const contents = fs.readFileSync( potPath, 'utf8' );
const pattern = /"POT-Creation-Date: [^"\n]*\\n"/;
const replacement = `"POT-Creation-Date: ${ creationDate }\\n"`;

if ( ! pattern.test( contents ) ) {
	throw new Error( 'languages/brand-master.pot is missing its POT-Creation-Date header.' );
}

const normalized = contents.replace( pattern, replacement );
if ( normalized !== contents ) {
	fs.writeFileSync( potPath, normalized );
}

console.log( `Translation template timestamp normalized to ${ creationDate }.` );
