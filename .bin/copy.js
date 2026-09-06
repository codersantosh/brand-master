const ncp = require('ncp');
const rimraf = require('rimraf');
const fs = require('fs');

const source = process.cwd();
const destination = 'deploy';
const packageDestination = `${destination}/brand-master`;

// Delete the destination directory
rimraf(destination, (error) => {
	if (error) {
		console.error('Error occurred:', error);
	} else {
		fs.mkdirSync(packageDestination, { recursive: true });

		// Copy the files and directories
		ncp(
			source,
			packageDestination,
			{
				filter: (file) =>
					!file.match(
						/(?:^|[/\\])(?:deploy|src|node_modules|\.git|\.bin|tests|vendor|\.github)(?:[/\\]|$)|(?:^|[/\\])(?:\.babelrc|\.gitignore|\.phpunit\.result\.cache|composer\.json|composer\.lock|package\.json|package-lock\.json|phpcs\.xml\.dist|phpunit\.xml\.dist|readme\.md|js\.pot|translation-js\.php)$/
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
