<script setup lang="ts">
import AboutFixtures from '@/modules/fixturePredictions/components/AboutFixtures.vue';
import PredictionsModal from '@/modules/fixturePredictions/components/PredictionsModal.vue';
import LeaderboardTable from '@/modules/fixturePredictions/components/LeaderboardTable.vue';
import FixturesTable from '@/modules/fixturePredictions/components/FixturesTable.vue';
import FixtureFilters from '@/modules/fixturePredictions/components/FixtureFilters.vue';
import HeadToHeadModal from '@/modules/fixturePredictions/components/HeadToHeadModal.vue';
import { computed, provide } from 'vue';
import { useTables } from '@/modules/fixturePredictions/composables/useTables';
import TopButtons from '@/modules/fixturePredictions/components/TopButtons.vue';
import TabNavigationButton from '@/modules/fixturePredictions/components/TabNavigationButton.vue';
import Tab from '@/modules/fixturePredictions/components/TabView.vue';
import { useFilters } from '@/modules/fixturePredictions/composables/useFilters';
import { useHeadToHead } from '@/modules/fixturePredictions/composables/useHeadToHead';
import { usePredictions } from '@/modules/fixturePredictions/composables/usePredictions';

const filters = useFilters();
const h2h = useHeadToHead();
const tables = useTables(filters, h2h);
const predictions = usePredictions(tables);
provide('filters', filters);
provide('tables', tables);
provide('h2h', h2h);
provide('predictions', predictions);

tables.loadFixtures();

const disablePredictions = computed(() => {
  return tables.isLoadingTables.value || tables.fixtures.value?.length === 0;
});
</script>

<template>
  <div class="ov-center">
    <div class="container">

      <FixtureFilters/>
      <HeadToHeadModal/>
      <PredictionsModal/>

      <TopButtons :disablePredictions/>

      <nav>
        <div class="nav nav-tabs justify-content-center" id="nav-tab" role="tablist">
          <TabNavigationButton :table="'matches'" text="Matches" :active="true"/>
          <TabNavigationButton :table="'leaderboard'" text="Leaderboard"/>
        </div>
      </nav>

      <div class="tab-content">
        <div v-if="tables.isLoadingTables.value" class="spinner-border text-primary mt-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <Tab :table="'matches'" :active="true">
          <FixturesTable/>
        </Tab>
        <Tab :table="'leaderboard'">
          <LeaderboardTable/>
        </Tab>
      </div>

      <AboutFixtures/>

    </div>
  </div>
</template>
