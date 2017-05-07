<template>
	<div>
	    <input type="checkbox" class="myswitch" id="switch">
	    <label for='switch' data-on='on' data-off='off'></label>
	</div>
</template>

<script>

export default {
  name: 'myswitch',
  data () {
    return { 
    }
  },
  computed: {
     

  },
  components:{
  
  },
  methods:{
     
  },
  mounted(){
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="stylus" rel="stylesheet/stylus" >
@import "./myswitch.styl" 
</style>

