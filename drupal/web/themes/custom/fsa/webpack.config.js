const path = require('path')

// Plugins
const ExtractCSSPlugin = require('extract-text-webpack-plugin')
const SpritePlugin = require('svg-sprite-loader/plugin')
const HtmlWebpackPlugin = require('html-webpack-plugin')
const CleanWebpackPlugin = require('clean-webpack-plugin')

module.exports = {
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
  },
  plugins: [
    // Clean dist folder before building
    new CleanWebpackPlugin(['dist']),

    // Extract CSS to its own file
    new ExtractCSSPlugin({
      filename: '[name].css'
    }),

    // Create SVG sprite
    new SpritePlugin(),

    // Create a custom template for styleguide
    new HtmlWebpackPlugin({
      template: 'styleguide/template.js'
    })
  ]
}
