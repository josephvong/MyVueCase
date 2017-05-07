<template>
	<div class="stars">
	  <input type="radio" name="star" value="1" id="star-1" class="star">
    <label for="star-1"></label>
    <input type="radio" name="star" value="1" id="star-2" class="star">
    <label for="star-2"></label>
    <input type="radio" name="star" value="1" id="star-3" class="star">
    <label for="star-3"></label>
    <input type="radio" name="star" value="1" id="star-4" class="star">
    <label for="star-4"></label>
    <input type="radio" name="star" value="1" id="star-5" class="star">
    <label for="star-5"></label>

    <span class="description" data-option-1="很差" data-option-2="不行" data-option-3="凑合" data-option-4="不错" data-option-5="很好">aa</span>
  </div>
</template>

<script>

export default {
  name: 'star',
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
@import "./star.styl" 
</style>

