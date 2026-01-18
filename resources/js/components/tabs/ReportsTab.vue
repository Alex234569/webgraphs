<template>
  <div class="reports-tab">
    <div v-if="!isAdmin" class="locked-message">
      <el-icon :size="64" color="#909399"><Lock /></el-icon>
      <p>Раздел доступен только администраторам</p>
    </div>

    <div v-else class="reports-container">
      <div class="filters-bar">
        <el-select v-model="reportType" placeholder="Выберите отчет" style="width: 250px" @change="fetchPreview">
          <el-option label="Финансовый итог (по месяцам)" value="monthly-summary" />
          <el-option label="План vs Факт" value="budget-plan-fact" />
          <el-option label="Операции: Расходы" value="operations-expenses" />
          <el-option label="Операции: Доходы" value="operations-revenues" />
        </el-select>

        <el-date-picker
          v-model="dateRange"
          type="monthrange"
          range-separator="—"
          start-placeholder="С"
          end-placeholder="По"
          format="YYYY-MM"
          value-format="YYYY-MM"
          @change="fetchPreview"
          v-if="!isOperationsReport"
        />

        <el-date-picker
          v-model="dateRange"
          type="daterange"
          range-separator="—"
          start-placeholder="С"
          end-placeholder="По"
          format="DD.MM.YYYY"
          value-format="YYYY-MM-DD"
          @change="fetchPreview"
          v-else
        />

        <div class="export-buttons">
          <el-radio-group v-model="exportFormat" size="small" style="margin-right: 15px">
            <el-radio-button label="csv">CSV</el-radio-button>
            <el-radio-button label="xlsx">Excel</el-radio-button>
          </el-radio-group>
          <el-button type="primary" :icon="Download" @click="exportData" :disabled="!reportType">
            Экспорт
          </el-button>
        </div>
      </div>

      <el-card class="preview-card" shadow="hover">
        <template #header>
          <div class="card-header">
            <h3>Предпросмотр данных</h3>
          </div>
        </template>

        <el-table :data="previewData" v-loading="loading" stripe border style="width: 100%" height="500">
          <template v-if="reportType === 'monthly-summary'">
            <el-table-column prop="year" label="Год" width="80" />
            <el-table-column prop="month" label="Месяц" width="120">
              <template #default="scope">{{ getMonthName(scope.row.month) }}</template>
            </el-table-column>
            <el-table-column prop="revenue_total" label="Доход">
              <template #default="scope">{{ formatCurrency(scope.row.revenue_total) }}</template>
            </el-table-column>
            <el-table-column prop="expense_total" label="Расход">
              <template #default="scope">{{ formatCurrency(scope.row.expense_total) }}</template>
            </el-table-column>
            <el-table-column prop="profit_total" label="Прибыль">
              <template #default="scope">{{ formatCurrency(scope.row.profit_total) }}</template>
            </el-table-column>
            <el-table-column prop="profit_margin_pct" label="Маржа %">
              <template #default="scope">{{ scope.row.profit_margin_pct }}%</template>
            </el-table-column>
          </template>

          <template v-else-if="reportType === 'budget-plan-fact'">
            <el-table-column prop="year" label="Год" width="80" />
            <el-table-column prop="month" label="Месяц" width="120">
              <template #default="scope">{{ getMonthName(scope.row.month) }}</template>
            </el-table-column>
            <el-table-column prop="category" label="Категория" />
            <el-table-column prop="planned_amount" label="План">
              <template #default="scope">{{ formatCurrency(scope.row.planned_amount) }}</template>
            </el-table-column>
            <el-table-column prop="actual_amount" label="Факт">
              <template #default="scope">{{ formatCurrency(scope.row.actual_amount) }}</template>
            </el-table-column>
            <el-table-column prop="delta_amount" label="Дельта">
              <template #default="scope">{{ formatCurrency(scope.row.delta_amount) }}</template>
            </el-table-column>
          </template>

          <template v-else-if="isOperationsReport">
            <el-table-column prop="date" label="Дата" width="120" />
            <el-table-column prop="category" label="Категория" v-if="reportType === 'operations-expenses'" />
            <el-table-column prop="amount" label="Сумма">
              <template #default="scope">{{ formatCurrency(scope.row.amount) }}</template>
            </el-table-column>
            <el-table-column prop="description" label="Описание" />
          </template>

          <template #empty>
            <div v-if="isOperationsReport">
              <el-empty description="Для этого типа отчета предпросмотр недоступен. Используйте экспорт.">
                <template #image>
                  <el-icon :size="64" color="#909399"><Download /></el-icon>
                </template>
              </el-empty>
            </div>
            <el-empty v-else description="Выберите тип отчета или измените фильтры" />
          </template>
        </el-table>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useAuth } from '../../composables/useAuth';
import { Lock, Download } from '@element-plus/icons-vue';
import axios from 'axios';
import { ElMessage } from 'element-plus';

const { isAdmin } = useAuth();
const reportType = ref('monthly-summary');
const exportFormat = ref('csv');
const dateRange = ref(null);
const previewData = ref([]);
const loading = ref(false);

const isOperationsReport = computed(() => reportType.value?.startsWith('operations-'));

// Названия месяцев
const getMonthName = (month) => {
  const months = [
    'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
    'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
  ];
  return months[month - 1] || month;
};

// Функция форматирования валюты
const formatCurrency = (value) => {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'RUB',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
};

const fetchPreview = async () => {
  if (!reportType.value || (reportType.value.startsWith('operations-'))) {
     if (reportType.value.startsWith('operations-')) {
        previewData.value = []; // Очищаем превью для операций, так как в задаче сказано "export only" для них
     }
  }

  if (reportType.value === 'operations-expenses' || reportType.value === 'operations-revenues') {
      return;
  }

  try {
    loading.value = true;
    const params = {};
    if (dateRange.value) {
      params.from = dateRange.value[0];
      params.to = dateRange.value[1];
    }

    const endpoint = `/api/reports/${reportType.value}`;
    const response = await axios.get(endpoint, { params });
    previewData.value = response.data;
  } catch (error) {
    console.error('Ошибка загрузки превью:', error);
    ElMessage.error('Не удалось загрузить данные для превью');
  } finally {
    loading.value = false;
  }
};

const exportData = () => {
  const params = new URLSearchParams();
  if (dateRange.value) {
    params.append('from', dateRange.value[0]);
    params.append('to', dateRange.value[1]);
  }
  params.append('format', exportFormat.value);

  let url = '';
  if (reportType.value === 'monthly-summary') {
    url = `/api/reports/monthly-summary/export?${params.toString()}`;
  } else if (reportType.value === 'budget-plan-fact') {
    url = `/api/reports/budget-plan-fact/export?${params.toString()}`;
  } else if (reportType.value.startsWith('operations-')) {
    const type = reportType.value.split('-')[1];
    params.set('type', type);
    url = `/api/reports/operations/export?${params.toString()}`;
  }

  // Создаем временную ссылку для скачивания
  window.open(url, '_blank');
};

// Первичная загрузка
if (isAdmin.value) {
  fetchPreview();
}

watch(reportType, (newVal, oldVal) => {
    // Если переключились между типами отчетов с разными форматами дат, сбрасываем фильтр
    const wasOps = oldVal?.startsWith('operations-');
    const isOps = newVal?.startsWith('operations-');
    if (wasOps !== isOps) {
        dateRange.value = null;
    }
    fetchPreview();
});
</script>

<style lang="less" scoped>
.reports-tab {
  padding-top: 20px;

  .locked-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 100px 0;
    color: #909399;

    p {
      margin-top: 15px;
      font-size: 18px;
    }
  }

  .filters-bar {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    align-items: center;
    flex-wrap: wrap;

    .export-buttons {
      margin-left: auto;
    }
  }

  .preview-card {
    .card-header {
      h3 {
        margin: 0;
      }
    }
  }
}
</style>
