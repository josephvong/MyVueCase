<template>
  <div class="app">
    <view-box ref="viewBox" bodyPaddingTop="2.5rem">
     <x-header slot="header"  v-bind:title="this.$route.name" class="main-header"
                :left-options= "{showBack: this.$route.name=='home'?false:true,}"
      > </x-header>
      <div>
       <router-view></router-view>
      </div>
      <tabbar slot="bottom">
        <tabbar-item  link="/" icon-class="success"   >
          <icon slot="icon" type="success" ></icon>
          <span slot="label">首  页</span>
        </tabbar-item>
        <tabbar-item link="/casegroup">
          <img slot="icon" src="./assets/logo.png" />
          <span slot="label">案例</span>
        </tabbar-item>
        <tabbar-item>
          <span slot="label">简介</span>
        </tabbar-item>
      </tabbar>
    </view-box>
  </div>
</template>

<script>
import vuexI18n from 'vuex-i18n'
import { ViewBox, XHeader,Tabbar,TabbarItem,Icon} from 'vux'
export default {
  name: 'app',
  data(){
    return {
      isHome:this.$route.name=='home'?true:false
    }
  },
  computed:{
    cuspath(){
      return this.$store.state.router
    },
    count(){
      return this.$store.state.count
    }
  },
  components:{
    ViewBox,
    XHeader,
    Tabbar,
    TabbarItem,
    Icon
  },
  mounted(){

  }
}
</script>

<style lang="stylus" rel="stylesheet/stylus">
.app
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  font-size:1rem;
  height:100%;
  .main-header
    width:100%;
    height:2.3rem;
    position:absolute;
    left:0;
    top:0;
    z-index:100;
</style>
