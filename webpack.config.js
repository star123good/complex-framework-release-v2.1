const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const ExtractCssChunks = require('extract-css-chunks-webpack-plugin');
const devMode = process.env.NODE_ENV !== 'production';

module.exports = [
  {
    entry: './frontend/resources/js/app.js',
    output: {
      filename: devMode ? 'app.js' : 'app.min.js',
      path: path.resolve(__dirname, 'frontend/public/js'),
    },
    mode: devMode ? 'development' : 'production',
  },
  {
    entry: './frontend/resources/js/service-worker.js',
    output: {
      filename: devMode ? 'service-worker.js' : 'service-worker.min.js',
      path: path.resolve(__dirname, 'frontend/public/js'),
    },
    mode: devMode ? 'development' : 'production', // "production" | "development" | "none"
    devtool: 'source-map', //  'inline-source-map' | 'source-map'
  },
];