<script setup lang="ts">
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL;
</script>

<template>
  <div class="ov-center">
    <h4>Welcome to my site!</h4>

    <p>Currently there is only a Football Predictions game. Maybe there will be more to come, who knows. So stay tuned ;)</p>
    <p>If you'd like to contact me for some reason, you're welcome.</p>
    <br />

    <h5>Contacts:</h5>
    <p><i class="bi bi-envelope-at"></i> <a href= "mailto:oleg.vartanov1997@gmail.com">oleg.vartanov1997@gmail.com</a></p>
    <br />

    <h5>Resources:</h5>
    <p>
      <i class="bi bi-file-text"></i> API Documentation <a :href="apiBaseUrl+'/doc'">GUI</a>
      , <a :href="apiBaseUrl+'/doc.json'">JSON</a>
    </p>
    <i class="bi bi-github"></i> This app's <a href="https://github.com/Oleg-Vartanov/Voleg">GitHub</a><br />
  </div>
</template>