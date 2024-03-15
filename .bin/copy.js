const ncp = require('ncp');
const rimraf = require('rimraf');

const source = process.cwd();
const destination = 'deploy';

// Delete the destination directory
rimraf(destination, (error) => {
	if (error) {
		console.error('Error occurred:', error);
	} else {
		// Copy the files and directories
		ncp(
			source,
			destination,
			{
				filter: (file) =>
					!file.match(
						new RegExp(
							destination +
								'|src|node_modules|.git|.bin|.babelrc|.gitignore|package.json|package-lock.json|js.pot|translation-js.php'
						)
					),
			},
			(error) => {
				if (error) {
					console.error('Error occurred:', error);
				} else {
					console.log('Files copied');
				}
			}
		);
	}
});
