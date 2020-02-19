const customProperties = require('postcss-custom-properties')({ preserve: false });
const postcssImport = require('postcss-import')({ path: './src/' });
const autoprefixer = require('autoprefixer');
const hexrgba = require('postcss-hexrgba');
const imageSizes = require('postcss-image-sizes');
const postcssCustomMedia = require('postcss-custom-media')();

module.exports = {
  plugins: [postcssImport, customProperties, autoprefixer, hexrgba, imageSizes, postcssCustomMedia],
};
