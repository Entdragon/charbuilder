// webpack.config.js
const path = require('path');

module.exports = {
  mode: 'production',

  entry: {
    'gifts.bundle': path.resolve(__dirname, 'assets/js/src/gifts/index.js'),
    'core.bundle':  path.resolve(__dirname, 'assets/js/src/core/index.js'),
  },

  output: {
    filename: '[name].js',
    path:     path.resolve(__dirname, 'assets/js/dist'),
  },

  module: {
    rules: [
      {
        test: /\.js$/,
        include: path.resolve(__dirname, 'assets/js/src'),
        use: {
          loader: 'babel-loader',
        }
      }
    ]
  },

  resolve: {
    extensions: ['.js']
  },

  devtool: 'source-map'
};
