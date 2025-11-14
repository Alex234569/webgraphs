<template>
  <div id="app">
    <el-container>
      <!-- Хедер только для авторизованных пользователей -->
      <el-header v-if="isAuthenticated()" class="app-header">
        <div class="header-content">
          <h1 class="logo">WebGraphs</h1>

          <el-menu
            mode="horizontal"
            :default-active="activeMenu"
            router
            class="main-menu"
          >
            <el-menu-item index="/">Главная</el-menu-item>
            <el-menu-item index="/graphs">Графики</el-menu-item>
          </el-menu>

          <div class="user-section">
            <el-dropdown @command="handleCommand">
              <span class="user-dropdown">
                <el-avatar :size="32" :icon="UserFilled" />
                <span class="user-name">{{ user?.name }}</span>
                <el-tag v-if="isAdmin()" type="warning" size="small">Admin</el-tag>
              </span>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item disabled>
                    <div class="user-info">
                      <div>{{ user?.email }}</div>
                      <small>{{ user?.role }}</small>
                    </div>
                  </el-dropdown-item>
                  <el-dropdown-item divided command="logout">
                    <el-icon><SwitchButton /></el-icon>
                    Выход
                  </el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </div>
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
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { UserFilled, SwitchButton } from '@element-plus/icons-vue';
import { useAuth } from './composables/useAuth';

const route = useRoute();
const router = useRouter();
const { user, isAuthenticated, isAdmin, logout } = useAuth();

const activeMenu = computed(() => route.path);

const handleCommand = async (command) => {
  if (command === 'logout') {
    const result = await logout();
    if (result.success) {
      ElMessage.success('Вы вышли из системы');
      router.push('/login');
    }
  }
};
</script>

<style lang="less" scoped>
.app-header {
  background: #fff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  padding: 0;

  .header-content {
    max-width: 1400px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    height: 100%;
    gap: 20px;

    .logo {
      font-size: 24px;
      font-weight: bold;
      color: #409eff;
      margin: 0;
      flex-shrink: 0;
    }

    .main-menu {
      flex: 1;
      border: none;
    }

    .user-section {
      flex-shrink: 0;
      margin-left: auto;

      .user-dropdown {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        padding: 8px 12px;
        border-radius: 4px;
        transition: background-color 0.3s;

        &:hover {
          background-color: #f5f7fa;
        }

        .user-name {
          font-weight: 500;
          color: #303133;
        }
      }

      .user-info {
        padding: 4px 0;

        div {
          color: #303133;
          font-weight: 500;
        }

        small {
          color: #909399;
          text-transform: uppercase;
        }
      }
    }
  }
}

:deep(.el-main) {
  padding: 0;
}
</style>
