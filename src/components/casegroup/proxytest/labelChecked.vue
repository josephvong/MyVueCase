<template>
	<div>
	    <fieldset>
		  <input type="radio" id="radio1" name="radio">
		  <label for="radio1">Radio 1</label>
		  
		  <input type="radio" id="radio2" name="radio">
		  <label for="radio2">Radio 2</label>
		</fieldset>
		<fieldset>
		  <input type="checkbox" id="cb-1">
		  <label for="cb-1">Checkbox 1</label>

		  <input type="checkbox" id="cb-2">
		  <label for="cb-2">Checkbox 2</label>
		</fieldset>
	</div>
</template>

<script>

export default {
  name: 'proxytest',
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
@import "./radio-checkbox.styl"
  
</style>

