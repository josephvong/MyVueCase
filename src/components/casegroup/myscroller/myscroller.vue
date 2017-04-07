<template>
  <div class="myscroller">
    <scroller lock-x height="100%" @on-scroll="onScroll"  ref="scrollerEvent"
              @on-pullup-loading="onPullup"
               :use-pullup="true"
               v-bind:pullup-config="pullupConf"
    >
      <div>
        <p v-for="i in scrollerData">placeholder {{i}}</p>
      </div>
    </scroller>
  </div>
</template>

<script>
import { Scroller,ViewBox } from 'vux'
export default {
  name: 'myscroller',
  data () {
    return {
      scrollerData:20,
      pullupConf:{
        //content: '上拉加载更多',
        pullUpHeight: 60,
        height: 80,
        autoRefresh: true,
        downContent: '上拉加载更多',
        upContent: '上拉加载更多',
        loadingContent: '加载中...',
      }
    }
  },
  methods:{
    onScroll (pos) {
      //console.log('on scroll', pos)
      this.scrollTop = pos.top
    },
    onPullup(pos){
      this.scrollerData+=10;
      this.$nextTick(()=>{
        this.$refs.scrollerEvent.donePullup()
      })

    },
    scrollerInit(){
      this.$nextTick(() => {
        this.$refs.scrollerEvent.reset({
            top: 0
          })
      })
    }
  },
  computed:{

  },
  components:{
    Scroller,ViewBox
  },
  mounted(){
    this.scrollerInit();

  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped  lang="stylus" rel="stylesheet/stylus" >
.myscroller
  position:absolute
  left:0
  right:0
  top:2.5rem
  bottom:3rem


</style>
