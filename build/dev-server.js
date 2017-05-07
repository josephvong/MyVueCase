require('./check-versions')()

var config = require('../config')
if (!process.env.NODE_ENV) {
  process.env.NODE_ENV = JSON.parse(config.dev.env.NODE_ENV)
}

var opn = require('opn')  // 强制打开浏览器并跳转指定url 的插件
var path = require('path') // nodejs 路径解析工具
var express = require('express')  // express框架
var webpack = require('webpack')  // webpack
var proxyMiddleware = require('http-proxy-middleware')  // 使用 proxyTable 配置代理转换
var webpackConfig = require('./webpack.dev.conf')  // 使用 dev 环境的 webpack 配置

// default port where dev server listens for incoming traffic 如果没有指定运行端口，使用config.dev 指定的port端口
var port = process.env.PORT || config.dev.port
// automatically open browser, if not set will be false
var autoOpenBrowser = !!config.dev.autoOpenBrowser   // 自动打开浏览器
// Define HTTP proxies to your custom API backend
// https://github.com/chimurai/http-proxy-middleware
var proxyTable = config.dev.proxyTable   // http 的代理配置，自定义后端接口

var app = express()  // 开启 express 服务器
var compiler = webpack(webpackConfig) // 启动webpack 编译编写的代码

// 将编译后的 文件 放在内存中运行的插件
var devMiddleware = require('webpack-dev-middleware')(compiler, {
  publicPath: webpackConfig.output.publicPath,
  quiet: true
})

// 热加载插件
var hotMiddleware = require('webpack-hot-middleware')(compiler, {
  log: () => {}
})
// force page reload when html-webpack-plugin template changes  （强制刷新插件）
compiler.plugin('compilation', function (compilation) {
  compilation.plugin('html-webpack-plugin-after-emit', function (data, cb) {
    hotMiddleware.publish({ action: 'reload' })
    cb()
  })
})

// proxy api requests
//将 proxyTable 中的请求配置挂在到启动的 express 服务上
Object.keys(proxyTable).forEach(function (context) {
  var options = proxyTable[context]
  if (typeof options === 'string') {
    options = { target: options }
  }
  app.use(proxyMiddleware(options.filter || context, options))
})

// handle fallback for HTML5 history API   （express服务器使用 h5 histrory 历史匹配 插件来匹配vue的路由）
app.use(require('connect-history-api-fallback')())

// serve webpack bundle output  （将 webpack 中 临时编译的文件挂在到 express 服务器上 (dev环境中) ）
app.use(devMiddleware)

// enable hot-reload and state-preserving
// compilation error display （express 服务器 挂在 热加载插件，并且当出错时在页面直接显示错误信息）
app.use(hotMiddleware)

// serve pure static assets
var staticPath = path.posix.join(config.dev.assetsPublicPath, config.dev.assetsSubDirectory)  // 拼接 static文件夹路径
app.use(staticPath, express.static('./static'))  // 让 express 服务器 操作 static 文件夹内的静态资源文件


var uri = 'http://localhost:' + port

devMiddleware.waitUntilValid(function () {
  console.log('> Listening at ' + uri + '\n')
})

// 让我们这个 express 服务监听 port 的请求，并且将此服务作为 dev-server.js 的接口暴露
module.exports = app.listen(port, function (err) {
  if (err) {
    console.log(err)
    return
  }

  // when env is testing, don't need open it
  if (autoOpenBrowser && process.env.NODE_ENV !== 'testing') {
    opn(uri)
  }
})
