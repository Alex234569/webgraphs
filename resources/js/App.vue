<template>
  <div id="app">
    <el-container>
      <el-header class="app-header">
        <div class="header-content">
          <h1 class="logo">WebGraphs</h1>
          <el-menu
            mode="horizontal"
            :default-active="activeMenu"
            router
          >
            <el-menu-item index="/">Главная</el-menu-item>
            <el-menu-item index="/graphs">Графики</el-menu-item>
          </el-menu>
        </div>
      </el-header>

      <el-main>
        <router-view />
      </el-main>
    </el-container>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const activeMenu = computed(() => route.path);
</script>

<style lang="less" scoped>
.app-header {
  background: #fff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  padding: 0;

  .header-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    height: 100%;
    gap: 40px;

    .logo {
      font-size: 24px;
      font-weight: bold;
      color: #409eff;
      margin: 0;
    }
  }
}

:deep(.el-main) {
  padding: 0;
}
</style>
