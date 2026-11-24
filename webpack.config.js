const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );

const rules = defaultConfig.module.rules.map( ( rule ) => {
	if ( ! rule.use || ! Array.isArray( rule.use ) ) {
		return rule;
	}

	return {
		...rule,
		use: rule.use.map( ( loader ) => {
			if ( loader.loader && loader.loader.includes( 'sass-loader' ) ) {
				return {
					...loader,
					options: {
						...loader.options,
						api: 'modern',
					},
				};
			}

			return loader;
		} ),
	};
} );

module.exports = {
	...defaultConfig,
	optimization: {
		...defaultConfig.optimization,
		splitChunks: {
			...defaultConfig.optimization.splitChunks,
			cacheGroups: {
				...defaultConfig.optimization.splitChunks.cacheGroups,
				// Keep the complete icon-picker catalog out of the initial admin bundle.
				'brand-master-font-awesome': {
					test: /[\\/]react-icons[\\/]fa[\\/]/,
					name: 'brand-master-font-awesome',
					chunks: 'async',
					enforce: true,
				},
			},
		},
	},
	module: {
		...defaultConfig.module,
		rules,
	},
};
