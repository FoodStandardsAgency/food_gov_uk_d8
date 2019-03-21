const path = require('path')

// Plugins
const ExtractCSSPlugin = require('extract-text-webpack-plugin')
const SpritePlugin = require('svg-sprite-loader/plugin')
const HtmlWebpackPlugin = require('html-webpack-plugin')
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin')
const FileManagerPlugin = require('filemanager-webpack-plugin')

var config = {
  context: path.resolve(__dirname, 'src'),
  entry: {
    app: './index.js',
    editor: './editor.js',
    styleguide: './styleguide/index.js'
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['env']
          }
        }
      },
      // {
      //   test: /\.js$/,
      //   // resourceQuery: /styleguide/,
      //   include: /(component)/,
      //   use: [
      //     {
      //       loader: 'raw-loader'
      //     }
      //   ]
      // },
      {
        test: /\.css$/,
        exclude: /(component|styleguide|helper)/,
        use: ExtractCSSPlugin.extract({
          use: [
            {
              loader: 'css-loader',
              options: { importLoaders: 1 }
            },
            'postcss-loader'
          ]
        })
      },
      {
        test: /\.css$/,
        include: /(component|helper)/,
        use: [
          {
            loader: 'raw-loader'
          }
        ]
      },
      {
        test: /\.css$/,
        include: /styleguide/,
        use: [
          {
            loader: 'css-loader',
            options: {
              importLoaders: 1,
              modules: 1,
              localIdentName: '[name]__[local]___[hash:base64:5]'
            }
          },
          {
            loader: 'postcss-loader',
            options: {
              config: {
                path: './styleguide/postcss.config.js'
              }
            }
          }
        ]
      },
      {
        test: /\.(gif|png|jpe?g)$/i,
        use: [
          'file-loader?name=[path][name].[ext]',
          'image-webpack-loader'
        ]
      },
      {
        test: /\.svg$/,
        use: [
          {
            loader: 'svg-sprite-loader',
            options: { extract: true }
          },
          'svgo-loader'
        ]
      },
      {
        test: /\.html$/,
        use: {
          loader: 'html-loader'
        }
      },
      {
        test: /\.md$/,
        use: [
          {
            loader: 'html-loader'
          },
          {
            loader: 'markdown-loader'
          }
        ]
      }
    ]
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'dist')
  }
};

module.exports = (env, argv) => {
  config.plugins = [];

  let fileManagerPluginConfig = {
    onEnd: [
      {
        copy: [
          { source: './dist/editor.css', destination: './dist/styleguide/editor.css' },
          { source: './dist/app.css', destination: './dist/styleguide/app.css' },
          { source: './dist/app.js', destination: './dist/styleguide/app.js' },
          { source: './dist/editor.js', destination: './dist/styleguide/editor.js' },
          { source: './dist/styleguide.js', destination: './dist/styleguide/styleguide.js' },
          { source: './node_modules/clipboard/dist/clipboard.min.js', destination: './dist/clipboard.min.js' },
        ]
      }
    ]
  };

  if (argv.mode === 'production') {
    fileManagerPluginConfig.onStart = [
      {
        delete: [
          "./dist/"
        ]
      }
    ];
  }

  config.plugins = [
    // Before building clean dist folder. After building copy CSS and JS files
    // to styleguide directory for standalone serving. We also copy
    // clipboard.min.js to dist for usage with libraries.yml.
    new FileManagerPlugin(fileManagerPluginConfig)
  ]

  config.plugins = [
      ...config.plugins,

    // Extract CSS to its own file
    new ExtractCSSPlugin({
      filename: '[name].css'
    }),

    // new OptimizeCssAssetsPlugin({
    //   cssProcessorOptions: { discardComments: { removeAll: true } }
    // }),

    // Create SVG sprite
    new SpritePlugin(),

    // Create a custom template for styleguide.
    // We do not inject links to JS and CSS files here, but to copies
    // of these files in styleguide directory for standalone serving.
    new HtmlWebpackPlugin({
      template: 'styleguide/template.js',
      filename: 'styleguide/index.html',
      inject: false,
    })

  ]

  return config;
}
