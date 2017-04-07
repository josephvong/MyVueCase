import Vue from 'vue'
import Router from 'vue-router'
import home from '../components/home/home.vue'
import caseGroup from '../components/casegroup/caseGroup.vue'
import todos from '../components/casegroup/todos/todos.vue'
import myscroller from '../components/casegroup/myscroller/myscroller.vue'
Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/', name: 'home', component: home
    },
    {
    	path: '/casegroup', name: 'casegroup', component: caseGroup
    },
    {
    	path: '/casegroup/todos',name:'todos',component:todos
    },
    {
      path: '/casegroup/myscroller',name:'myscroller',component:myscroller
    }
  ]
})
