export function useHelper() {
  function predictionHomeScore(prediction, defaultValue: string = '-') {
    return prediction?.homeScore == null ? defaultValue : prediction.homeScore;
  }

  function predictionAwayScore(prediction, defaultValue: string = '-') {
    return prediction?.awayScore == null ? defaultValue : prediction.awayScore;
  }

  function colorClass(prediction) {
    switch (prediction?.points) {
      case 3: return 'text-success';
      case 1: return 'text-warning';
      case 0: return 'text-danger';
      default: return '';
    }
  }

  function fixtureDate(fixture) {
    const date = new Date(fixture.startAt);

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const hour = String(date.getHours()).padStart(2, '0');
    const minute = String(date.getMinutes()).padStart(2, '0');

    return { date: `${day}/${month}`, time: `${hour}:${minute}` };
  }

  return { predictionHomeScore, predictionAwayScore, colorClass, fixtureDate };
}
