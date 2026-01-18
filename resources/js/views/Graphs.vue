<template>
  <div class="graphs-page">
    <div class="container">
      <el-tabs v-model="activeTab">
        <el-tab-pane label="Финансы" name="finance">
          <FinanceTab :months="months" v-model:date-range="dateRange" />
        </el-tab-pane>

        <el-tab-pane label="Аналитика" name="analytics" :disabled="!isAdmin">
          <AnalyticsTab :months="months" v-model:date-range="dateRange" />
        </el-tab-pane>

        <el-tab-pane label="Отчёты" name="reports" :disabled="!isAdmin">
          <ReportsTab />
        </el-tab-pane>
      </el-tabs>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuth } from '../composables/useAuth';
import FinanceTab from '../components/tabs/FinanceTab.vue';
import AnalyticsTab from '../components/tabs/AnalyticsTab.vue';
import ReportsTab from '../components/tabs/ReportsTab.vue';

const { isAdmin } = useAuth();
const activeTab = ref('finance');

// Date Range
const dateRange = ref([
  new Date(new Date().setMonth(new Date().getMonth() - 6)),
  new Date()
]);

// Вычисление количества месяцев между датами
const months = computed(() => {
  if (!dateRange.value || dateRange.value.length !== 2) return 6;

  const [start, end] = dateRange.value;
  const monthsDiff = (end.getFullYear() - start.getFullYear()) * 12 +
                     (end.getMonth() - start.getMonth());
  return Math.max(1, monthsDiff);
});
</script>

<style lang="less" scoped>
.graphs-page {
  padding: 20px;

  .container {
    max-width: 1400px;
    margin: 0 auto;
  }
}
</style>
