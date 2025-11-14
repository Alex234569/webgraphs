<template>
  <div class="graphs-page">
    <div class="container">
      <el-card class="header-card">
        <h2 class="page-title">Финансовые графики</h2>
        <p class="page-description">
          Здесь будут отображаться интерактивные графики с финансовыми данными департамента
        </p>
      </el-card>

      <el-row :gutter="20" class="charts-grid">
        <el-col
          v-for="chart in charts"
          :key="chart.id"
          :xs="24"
          :sm="12"
          :lg="8"
        >
          <el-card class="chart-card" shadow="hover">
            <template #header>
              <h3>{{ chart.title }}</h3>
            </template>
            <p class="chart-description">{{ chart.description }}</p>
            <div class="chart-placeholder">
              <el-icon :size="48" color="#909399"><TrendCharts /></el-icon>
              <span>График будет здесь</span>
            </div>
          </el-card>
        </el-col>
      </el-row>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { TrendCharts } from '@element-plus/icons-vue';

const charts = ref([
  {
    id: 1,
    title: 'Доходы по месяцам',
    description: 'График доходов департамента за текущий год',
  },
  {
    id: 2,
    title: 'Расходы по категориям',
    description: 'Распределение расходов по основным категориям',
  },
  {
    id: 3,
    title: 'Прибыль',
    description: 'Динамика прибыли за последние 6 месяцев',
  },
  {
    id: 4,
    title: 'Бюджет vs Факт',
    description: 'Сравнение планового и фактического бюджета',
  },
  {
    id: 5,
    title: 'ROI по проектам',
    description: 'Возврат инвестиций по ключевым проектам',
  },
  {
    id: 6,
    title: 'Прогноз на квартал',
    description: 'Прогнозируемые показатели на следующий квартал',
  },
]);
</script>

<style lang="less" scoped>
.graphs-page {
  padding: 40px 20px;

  .container {
    max-width: 1400px;
    margin: 0 auto;

    .header-card {
      margin-bottom: 24px;

      .page-title {
        font-size: 28px;
        font-weight: bold;
        color: #303133;
        margin-bottom: 8px;
      }

      .page-description {
        font-size: 14px;
        color: #606266;
        margin: 0;
      }
    }

    .charts-grid {
      .chart-card {
        margin-bottom: 20px;
        height: 100%;

        h3 {
          margin: 0;
          font-size: 18px;
          font-weight: 600;
        }

        .chart-description {
          font-size: 14px;
          color: #909399;
          margin-bottom: 16px;
        }

        .chart-placeholder {
          background-color: #f5f7fa;
          border-radius: 8px;
          height: 200px;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          gap: 12px;

          span {
            color: #909399;
            font-size: 14px;
          }
        }
      }
    }
  }
}
</style>
