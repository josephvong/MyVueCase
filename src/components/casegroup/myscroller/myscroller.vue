<template>
  <div class="myscroller">
    <scroller lock-x height="100%" @on-scroll="onScroll"  ref="scrollerEvent"
              @on-pullup-loading="onPullup"
               :use-pullup="true"
               v-bind:pullup-config="pullupConf"
    >
      <div class="list-wrap">
         <!-- <p v-for="item in dataList">{{item.country_ch}}</p> -->
        <ul class="result-list" >
           <scrollerItem v-for="(item,index) in dataList"
                    v-bind:infoObj="item" v-bind:key="index"
                     
          >
          </scrollerItem>
        </ul>
      </div>
    </scroller>
  </div>
</template>

<script>
import axios from 'axios'
import 'es6-promise/auto' 
import 'url-search-params-polyfill'

import scrollerItem from './scrollerItem.vue'
import { Scroller } from 'vux'
export default {
  name: 'myscroller',
  data () {
    return {
      scrollerData:20,
      dataList:[], // 数据列表
      pageIndex:1,
      pullupConf:{
        pullUpHeight: 100,
        height: 80,
        autoRefresh: true,
        downContent: '加载中',
        upContent: '加载中',
        loadingContent: '加载中...',
      },
      stableData:{  // 列表加载API 固定参数
        "authparams":{"app_id":"343535","rtoken":"sldffyy9767","time":1489131067},
        "authmode":"app",
        "country":"意大利",// 国家名
        "cookie":"940158d239561338e"
      },

    }
  },
  methods:{
    onScroll (pos) { 
      this.scrollTop = pos.top
    },
    onPullup(pos){
      this.pageIndex+=1;
      this.dataLoad(this.pageIndex).then((res)=>{ 
         this.concatArr(res.data);
      })
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
    },
    dataLoad(page){
      let newObj = {};
      let data = Object.assign(newObj,this.stableData,{page:page})
      let params = new URLSearchParams();
      params.append('jparams',JSON.stringify(data))
      return axios.post('http://zyshi.9kacha.com/AutoRecommWines/toBfindWine/findWine.php',
        params
      )
    },
    concatArr(resObj){
      if(resObj.description=="ok"){
        let newArr = this.dataList;
        newArr = newArr.concat(resObj.jsonData);
        this.dataList = newArr;
        this.$nextTick(() => {
          this.$refs.scrollerEvent.reset()
        })
      }
    }, 
    

  },
  computed:{

  },
  components:{
    Scroller,scrollerItem
  },
  mounted(){

    this.scrollerInit();  

    this.dataLoad(this.pageIndex).then((res)=>{ 
       this.concatArr(res.data);
    }).catch((err)=>{
      alert(err);
    })

     
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
  .list-wrap
    width:100%;
    box-sizing: border-box;
    padding:1rem 1rem
    .result-list 
      box-sizing: border-box;
      padding-bottom:1rem; 
      margin:0;
 

</style>
