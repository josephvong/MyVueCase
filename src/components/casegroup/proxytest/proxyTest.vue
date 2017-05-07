<template>
  <div class="proxytest">
    <p style="font-size:1rem">本章节用于 测试 proxyTable 的用法  和 使用checked + label 编写个性化选择器</p>
    <br/>
    <labelChecked></labelChecked>
    <mySwitch></mySwitch>
    <multiSelect></multiSelect>
    <hr>
    <star></star>
    <br/>
    <br/>
    <br/>
    <br/>
  </div>
</template>

<script>
import axios from 'axios'
import 'es6-promise/auto'
import 'url-search-params-polyfill'

import labelChecked from './labelChecked' 
import mySwitch from './mySwitch' 
import multiSelect from './multiSelect'
import star from './star'
export default {
  name: 'proxytest',
  data () {
    return {
      apiStatic:{"authparams":{user_id:'467060', session_id:'pndhutha433tgolqat98o1f925'},"authmode":"app"},
    }
  },
  computed: {
     

  },
  components:{
    labelChecked,
    mySwitch,
    multiSelect,
    star
  },
  methods:{
    getRewardList(){
      let data = Object.assign({},this.apiStatic)
      let params = new URLSearchParams();
      params.append('jparams',JSON.stringify(data))
      axios.post("/api/getCjWinner.php",
        params
      ).then((res)=>{
        if(res.data.description=="ok"){
          console.log(res.data.data);
        }
      })
    }
  },
  mounted(){
    //this.getRewardList()
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="stylus" rel="stylesheet/stylus" >
.proxytest
  width:100%;
 
</style>
