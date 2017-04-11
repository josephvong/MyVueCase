// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.

import Vue from 'vue'
import App from './App'
import router from './router'
import appstore from './vuex/appstore'
import 'es6-promise/auto'
/*-------三种方法 使用ajax 发送请求---------*/
// vux 内置的 基于 axios 的ajax 处理插件 ，如果使用vux可以直接使用
//import {AjaxPlugin} from 'vux'
//Vue.use(AjaxPlugin) // 需要在main.js内 给Vue 定义使用

// 使用 Vue 基础的 vue-resource ，但目前此包已经停止维护
//import VueResource from "vue-resource"
//Vue.use(VueResource)  // 需要在main.js内 给Vue 定义使用

// 推荐使用 axios ,但如果低版本服务器，需要install es6-promise， 并调用 “require('es6-promise').polyfill()” 兼容没有 Promise 对象的浏览器
//import axios from 'axios' // 可以在任意地方 引入 axios， 并 调用 （无需用Vue.use()做全局定义 ）
/*-------三种方法 使用ajax 发送请求---------*/

/*--------------点击延时------------------*/
import FastClick from 'fastclick'
FastClick.attach(document.body)
/*--------------点击延时------------------*/




/*------全局公用函数--------
	如果有某些自定义工具函数可以挂在到全局Vue实例里面
		Vue.prototype.fnName = function(){.....} //在Vue 实例的prototype中定义
		在每个 组件里面，可以 用 “this.fnName()” 调用
--------------------------*/

Vue.config.productionTip = false

/* eslint-disable no-new */
new Vue({
  router,
  store:appstore,
  render:h=>h(App,{}),
  mounted(){
  	// 使用 axios 异步获取数据
  	router.push({"path":"/"});
   
  }
}).$mount("#app")
