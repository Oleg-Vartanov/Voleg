export default {
  format(date, format = 'YYYY-MM-DDThh:mm:ss') {
    const pad = (n) => n.toString().padStart(2, '0');

    const map = {
      YYYY: date.getFullYear(),
      MM: pad(date.getMonth() + 1),
      DD: pad(date.getDate()),
      hh: pad(date.getHours()),
      mm: pad(date.getMinutes()),
      ss: pad(date.getSeconds()),
    };

    return Object.entries(map).reduce((prev, entry) => prev.replace(...entry), format);
  },
  getTimezone(date) {
    const pad = (n) => n.toString().padStart(2, '0');

    // getTimezoneOffset returns minutes difference from UTC:
    const offsetMinutes = date.getTimezoneOffset();
    const offsetSign = offsetMinutes > 0 ? '-' : '+';
    const offsetHours = pad(Math.floor(Math.abs(offsetMinutes) / 60));
    const offsetMins = pad(Math.abs(offsetMinutes) % 60);

    return `${offsetSign}${offsetHours}:${offsetMins}`;
  }
}