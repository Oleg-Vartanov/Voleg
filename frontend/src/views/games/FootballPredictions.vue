<script setup lang="ts">
import { ref } from 'vue';
import Client from '@/modules/api-client.ts';
import { useTopAlerts } from "@/modules/top-alerts";
import { Alert } from "@/models/alert";

const topAlerts = useTopAlerts();
const isLoading = ref(true);

const fixtures = ref({});

Client.showFixtures()
  .then((response) => {
    fixtures.value = response.data;
  })
  .catch((axiosError) => {
    topAlerts.add(new Alert('Error during obtaining data.', 'danger', 10));
  })
  .finally(() => {
    isLoading.value = false;
  })

const matchdays = [...Array(38).keys()].map(i => i + 1);

</script>

<template>
  <div class="ov-center">
    <div v-if="isLoading" class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>

    <div class="container text-center">
      <div class="row align-items-start">

        <div class="col">
          <div class="accordion" id="accordion">
            <div v-for="matchday in matchdays" class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" :data-bs-target="'#collapse-'+matchday" aria-expanded="true" :aria-controls="'#collapse-'+matchday">
                  Matchday {{ matchday }}
                </button>
              </h2>
              <div :id="'collapse-'+matchday" class="accordion-collapse collapse" data-bs-parent="#accordion">
                <div class="accordion-body">
                  <strong>This is the first item's accordion body.</strong> It is shown by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col">
          <table class="table">
            <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Name</th>
              <th scope="col">Points</th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <th scope="row">1</th>
              <td>Mark</td>
              <td>13</td>
            </tr>
            <tr>
              <th scope="row">2</th>
              <td>Jacob</td>
              <td>25</td>
            </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>

  </div>
</template>