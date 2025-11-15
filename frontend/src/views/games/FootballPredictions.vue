<script setup lang="ts">
import ArrayHelper from "@/helpers/array-helper";
import { useAuth } from "@/modules/Core/auth";
import TeamLogo from "@/components/games/fooball-predictions/TeamLogo.vue";
import { useHelper } from "@/modules/FixturePredictions/composables/useHelper";
import { useFilters } from "@/modules/FixturePredictions/composables/useFilters";
import { useHeadToHead } from "@/modules/FixturePredictions/composables/useHeadToHead";
import { useTables } from "@/modules/FixturePredictions/composables/useTables";
import { usePredictions } from "@/modules/FixturePredictions/composables/usePredictions";

const { user } = useAuth();
const helper = useHelper();
const filters = useFilters();
const h2h = useHeadToHead();
const tables = useTables(filters, h2h);
const predictions = usePredictions(tables);

tables.loadFixtures();

function addUser(user) {
  h2h.addUser(user);
  tables.updateLoadedTables();
}
</script>

<template>
  <div class="ov-center">
    <div class="container">

      <!-- Top Buttons -->
      <div class="d-flex justify-content-center mb-3">
        <div class="btn-group me-2" role="group" aria-label="Top buttons">
          <button class="btn btn-outline-primary"
                  type="button"
                  data-bs-toggle="offcanvas"
                  data-bs-target="#offcanvasFilters"
                  aria-controls="offcanvasFilters"
          ><i class="bi bi-funnel"></i> Filters</button>
          <button class="btn btn-outline-primary"
                  data-bs-toggle="modal"
                  data-bs-target="#predictionsModal"
                  :disabled="tables.isLoading.value.fixtures || predictions.isLoading.value"
          ><i class="bi bi-magic"></i> Predict</button>
        </div>
        <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#aboutModal">
          <i class="bi bi-question-lg"></i>
        </button>
      </div>

      <!-- Nav -->
      <nav>
        <div class="nav nav-tabs justify-content-center" id="nav-tab" role="tablist">
          <button @click="tables.initTable('matches')" class="nav-link active" id="nav-matches-tab" type="button" role="tab"
            data-bs-toggle="tab" data-bs-target="#nav-matches" aria-controls="nav-matches" aria-selected="true">Matches
          </button>
          <button @click="tables.initTable('leaderboard')" class="nav-link" id="nav-leaderboard-tab" role="tab" type="button"
            data-bs-toggle="tab" data-bs-target="#nav-leaderboard" aria-controls="nav-leaderboard" aria-selected="false">Leaderboard
          </button>
        </div>
      </nav>

      <!-- Nav Tabs -->
      <div class="tab-content">

        <div v-if="tables.isLoading.value.fixtures || tables.isLoading.value.leaderboard" class="spinner-border text-primary mt-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>

        <!-- Matches -->
        <div class="tab-pane fade show active" id="nav-matches" role="tabpanel" aria-labelledby="nav-matches-tab" tabindex="0">
          <table v-if="!tables.isLoading.value.fixtures" class="table table-sm">
            <thead>
            <tr>
              <th scope="col">Match</th>
              <th scope="col">Score</th>
              <th scope="col">{{ h2h.users.value.length === 0 ? 'Prediction' : user.displayName }}</th>
              <th scope="col" v-if="h2h.users.value.length === 0">Points</th>
              <th scope="col" v-for="h2hUser in h2h.users.value">{{ h2hUser.displayName }}</th>
              <th scope="col">Start</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="fixture in tables.fixtures.value" :key="fixture.id">
              <td class="text-start">

                <!-- Teams -->
                <span>
                  <TeamLogo :teamName="fixture.homeTeam.name"></TeamLogo>
                  {{ fixture.homeTeam?.name || '=' }}
                </span><br>
                <span>
                  <TeamLogo :teamName="fixture.awayTeam.name"></TeamLogo>
                  {{ fixture.awayTeam.name }}
                </span>
              </td>

              <!-- Score -->
              <td>
                {{ fixture.homeScore === null ? '-' : fixture.homeScore }}<br>
                {{ fixture.awayScore === null ? '-' : fixture.awayScore }}
              </td>

              <!-- Current user prediction -->
              <td :class="helper.colorClass(predictions.getPrediction(fixture.id, user.id))">
                {{ predictions.getPrediction(fixture.id, user.id)?.homeScore ?? '-' }}<br>
                {{ predictions.getPrediction(fixture.id, user.id)?.awayScore ?? '-' }}
              </td>

              <!-- Points -->
              <td v-if="h2h.users.value.length === 0">
                {{ predictions.getPrediction(fixture.id, user.id)?.points ?? '-' }}
              </td>

              <!-- Head to Head users predictions -->
              <td v-for="user in h2h.users.value" :class="helper.colorClass(predictions.getPrediction(fixture.id, user.id))">
                {{ predictions.getPrediction(fixture.id, user.id)?.homeScore ?? '-' }}<br>
                {{ predictions.getPrediction(fixture.id, user.id)?.awayScore ?? '-' }}
              </td>

              <!-- Fixture Start -->
              <td v-for="{date, time} of [helper.fixtureDate(fixture)]">
                {{ time }}<br>{{ date }}
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <!-- Leaderboard -->
        <div class="tab-pane fade" id="nav-leaderboard" role="tabpanel" aria-labelledby="nav-leaderboard-tab" tabindex="0">
          <table v-if="!tables.isLoading.value.leaderboard" class="table table-sm">
            <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Name</th>
              <th scope="col">Period Points</th>
              <th scope="col">Total Points</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(user, index) in tables.leaderboard.value">
              <th scope="row">{{ index + 1 }}</th>
              <td>{{ user.user.displayName }}</td>
              <td>{{ user.periodPoints ?? '-' }}</td>
              <td>{{ user.totalPoints ?? '-' }}</td>
            </tr>
            </tbody>
          </table>
        </div>

      </div>

    </div>

    <!-- About Modal -->
    <div
      class="modal fade"
      id="aboutModal"
      tabindex="-1"
      aria-labelledby="aboutModalLabel"
    >
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5" id="aboutModalLabel">About</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <strong>Points:</strong><br>
              Exact Score: 3<br>
              Correct Result (Win/Draw/Loss): 1<br>
              No Prediction: 0<br><br>
              <i>Good luck!</i>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
    </div>

    <!-- Predictions Modal -->
    <div
      class="modal fade"
      id="predictionsModal"
      tabindex="-1"
      data-bs-backdrop="static"
      aria-labelledby="predictionsModalLabel"
    >
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="predictionsModalLabel">Predictions</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="makePredictionsForm" @submit.prevent="predictions.makePredictions">
              <table v-if="!tables.isLoading.value.fixtures" class="table">
                <thead>
                <tr>
                  <th scope="col">Match</th>
                  <th scope="col" style="width: 20%; min-width: 80px;">Home</th>
                  <th scope="col" style="width: 20%; min-width: 80px;">Away</th>
                </tr>
                </thead>
                <tbody>
                  <template v-for="fixture in tables.fixtures.value">
                    <tr v-if="new Date(fixture.startAt) > new Date()">
                      <td class="text-start">
                        <span>
                          <TeamLogo :teamName="fixture.homeTeam.name"></TeamLogo>
                          {{ fixture.homeTeam.name }}
                        </span>
                        <br>
                        <span>
                          <TeamLogo :teamName="fixture.awayTeam.name"></TeamLogo>
                          {{ fixture.awayTeam.name }}
                        </span>
                      </td>
                      <td>
                        <input class="form-control"
                               type="number"
                               min="0" max="99"
                               :name="'home-fixture-prediction-'+fixture.id"
                               :data-id="fixture.id"
                               data-side="home"
                               :value="helper.predictionHomeScore(predictions.getPrediction(fixture.id, user.id), '')">
                      </td>
                      <td>
                        <input class="form-control"
                               type="number"
                               min="0" max="99"
                               :name="'away-fixture-prediction-'+fixture.id"
                               :data-id="fixture.id"
                               data-side="away"
                               :value="helper.predictionAwayScore(predictions.getPrediction(fixture.id, user.id), '')">
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="closePredictionsModal" data-bs-dismiss="modal">Close</button>
            <button :disabled="predictions.isLoading.value" type="submit" form="makePredictionsForm" class="btn btn-primary">Save</button>
            <div v-if="predictions.isLoading.value" class="spinner-border text-primary mt-3" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- H2H Modal -->
    <div
      class="modal fade"
      id="go"
      tabindex="-1"
      aria-labelledby="h2hModalLabel"
      data-bs-backdrop="static"
    >
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="h2hModalLabel">Head To Head</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">

            <ul class="list-group list-group-flush mb-3">
              <li v-for="user in h2h.users.value" class="h2h-user list-group-item list-group-item-action" @click="h2h.removeUser(user)">
                {{ user.displayName }} (@{{ user.tag }}) <i class="bi bi-x-lg text-danger" style="font-size: 20px;"></i>
              </li>
            </ul>

            <div class="input-group mb-3 has-validation">
              <span class="input-group-text rounded-0" id="addon-wrapping">User Tag</span>
              <input v-model="h2h.input.value.value"
                     type="text"
                     :class="[h2h.input.value.error === '' ? '' : 'is-invalid']"
                     class="form-control"
                     aria-describedby="go-h2h validation-go-h2h"
                     @keyup.enter="h2h.input.value.value === '' || h2h.isLoading.value ? '' : h2h.searchUser()">
              <button @click="h2h.searchUser()"
                      :disabled="h2h.input.value.value === '' || h2h.isLoading.value"
                      class="btn btn btn-outline-primary rounded-0"
                      type="button"
                      id="go-h2h">Search</button>
              <div id="validation-go-h2h" class="invalid-feedback">{{ h2h.input.value.error }}</div>
            </div>

            <div v-if="h2h.isLoading.value" class="spinner-border text-primary mt-3" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>

            <ul class="list-group list-group-flush">
              <li v-for="user in h2h.searchUsers.value" class="h2h-user list-group-item list-group-item-action" @click="addUser(user);">
                {{ user.displayName }} (@{{ user.tag }}) <i class="bi bi-person-plus text-primary" style="font-size: 20px;"></i>
              </li>
            </ul>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Offcanvas -->
    <div class="offcanvas offcanvas-start"
         tabindex="-1"
         id="offcanvasFilters"
         aria-labelledby="offcanvas-filters-label"
    >
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvas-filters-label">Filters</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">

        <div class="d-flex justify-content-center">
          <div class="row">
            <div class="col-auto mb-2 p-1">
              <div class="input-group">
                <span class="input-group-text filter-date-text">Start</span>
                <input id="start" class="form-control filter-date-input" type="date" v-model="filters.start.value"/>
              </div>
            </div>

            <div class="col-auto mb-2 p-1">
              <div class="input-group">
                <span class="input-group-text filter-date-text">End</span>
                <input id="end" class="form-control filter-date-input" type="date" v-model="filters.end.value"/>
              </div>
            </div>

            <div class="col-auto mb-2 p-1">
              <div class="input-group">
                <span class="input-group-text filter-date-text">Competition</span>
                <select class="form-select" aria-label="Default select example" v-model="filters.competition.value">
                  <option value="PL">English Premier League</option>
                </select>
              </div>
            </div>

            <div class="col-auto mb-2 p-1">
              <div class="input-group">
                <span class="input-group-text filter-date-text">Season</span>
                <select class="form-select" v-model="filters.season.value">
                  <option v-for="year in ArrayHelper.range(2023, 2100)" :key="year" :value="year">
                    {{ year }}
                  </option>
                </select>
              </div>
            </div>

            <div class="col-auto mb-2 p-1">
              <button class="btn btn-primary"
                      data-bs-toggle="modal"
                      data-bs-target="#go"
              ><i class="bi bi-people-fill"></i> H2H</button>
            </div>

            <div class="col-auto mb-2 p-1">
              <button @click="tables.updateLoadedTables"
                      class="btn btn-outline-primary"
                      type="button"
                      :disabled="tables.isLoading.value.fixtures || tables.isLoading.value.leaderboard"
                      data-bs-dismiss="offcanvas"
              ><i class="bi bi-funnel"></i> Filter</button>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>

<style scoped>
.h2h-user {
  cursor: pointer;
}

.filter-date-input {
  width: 125px;
  padding-left: 6px;
}
.filter-date-text {
  padding: 0 5px 0 5px;
  min-width: 45px;
  display: flex; justify-content: center;
}
</style>