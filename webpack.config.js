const defaultConfig = require("./node_modules/@wordpress/scripts/config/webpack.config");
const path = require( 'path' );

module.exports = {
  ...defaultConfig,
  entry: {
	'block-editor-functions': path.resolve( process.cwd(), 'assets/src/js', 'block-editor-functions.js' ),
	'classic-editor-functions': path.resolve( process.cwd(), 'assets/src/js', 'classic-editor-functions.js' ),
	'bulk-export-functions': path.resolve( process.cwd(), 'assets/src/js', 'bulk-export-functions.js' ),
  },
  output: {
	filename: '[name].js',
	path: path.resolve( process.cwd(), 'assets/dist/js' ),
  },
};
