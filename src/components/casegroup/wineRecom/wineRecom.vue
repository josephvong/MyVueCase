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
        //"wine_id":'f7185ba7a6b413b970b23c66b445cd91',
        'recomm_sort':1,
        'only_policy' : 1,
      };
      $.ajax({
        url:localData.hostName+'dataApi/get_auto_recomm.php',
        data: JSON.stringify(newObj)
      })
      /*let newObj = {
        "wine_id":'f7185ba7a6b413b970b23c66b445cd91',
        'recomm_sort':1,
        'only_policy' : 1,
      };
      let data = Object.assign(newObj,this.stableData,{page:page})
      let params = new URLSearchParams();
      params.append('jparams',JSON.stringify(data))
      return axios.post('http://localhost/dataApi/get_auto_recomm.php',
        params
      )*/
    },
  },
  computed:{

  },
  components:{
    Popup
  },
  mounted(){
    //this.dataLoad()
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
