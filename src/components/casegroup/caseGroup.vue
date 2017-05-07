<template>
  <div class="caseGroup">
    <grid>
      <grid-item label="todos" link="/casegroup/todos"  >
        <img slot="icon" src="../../assets/logo.png" />
      </grid-item>
      <grid-item label="案例2" link="/casegroup/myscroller"  >
        <img slot="icon" src="../../assets/logo.png" />
      </grid-item>
      <grid-item label="案例3" link="/casegroup/proxytest"   >
        <img slot="icon" src="../../assets/logo.png" />
      </grid-item>
    </grid>
  </div>
</template>

<script>
import {Grid,GridItem,Icon} from 'vux'
export default {
  name: 'caseGroup',
  data () {
    return {

    }
  },
  components:{
    Grid,GridItem
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="stylus" rel="stylesheet/stylus" >

</style>
