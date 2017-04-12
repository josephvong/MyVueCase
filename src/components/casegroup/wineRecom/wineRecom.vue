<template>
  <div class="wineRecom">
      <popup v-model="showPopup" height="100%">
        <div class="popup1">
          <!-- AAAAAAADDDDD -->
        </div>
      </popup>
  </div>
</template>

<script>

import $ from "jquery"
import axios from 'axios'
import 'es6-promise/auto'
import 'url-search-params-polyfill'

import localData from 'apiUrl/location.js'

import {Popup} from 'vux'

export default {
  name: 'wineRecom',
  data () {
    return {
      showPopup:true

    }
  },
  methods:{
    dataLoad(){
      let newObj = {
        "wine_id":"074679831",
        "user_id":"414022",
        'year':'2015',
        "recomm_sort":1,
      };
      let data = Object.assign({},newObj)
      let params = new URLSearchParams();
      params.append('jparams',JSON.stringify(data))
      return axios.post(localData.hostName+'testApi/get_auto_recomm.php',
        params
      )
    },

    styleLoad(obj){
      function styleAxios(obj){
        let data = Object.assign({},obj)
        let params = new URLSearchParams();
        params.append('jparams',JSON.stringify(data))
        return axios.post(localData.hostName+'testApi/get_filter_wine_buy_detail.php',
          params
        )
      }
      return styleAxios
    }
  },
  computed:{

  },
  components:{
    Popup
  },
  mounted(){
    this.dataLoad().then((res)=>{
      let This=this;
      let arr = res.data.policyData.map((item,index)=>{
        return this.styleLoad(item.recomm_kv)
      })
      //console.log(arr);
      //return axios.all(arr)
    })/*.then(axios.spread((a,b,c)=>{
      console.log(a);
    }))*/
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped  lang="stylus" rel="stylesheet/stylus" >
.wineRecom
  position:absolute
  left:0
  right:0
  top:2.5rem
  bottom:3rem


</style>
