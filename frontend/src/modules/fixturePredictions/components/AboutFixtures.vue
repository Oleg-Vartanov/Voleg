<template>
  <div
    id="aboutModal"
    class="modal fade"
    tabindex="-1"
    aria-labelledby="aboutModalLabel"
  >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h1
            id="aboutModalLabel"
            class="modal-title fs-5"
          >
            About
          </h1>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          />
        </div>
        <div class="modal-body">
          <strong>Points:</strong><br>
          Exact Score: 3<br>
          Correct Result (Win/Draw/Loss): 1<br>
          No Prediction: 0<br><br>
          <i>Good luck!</i>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
