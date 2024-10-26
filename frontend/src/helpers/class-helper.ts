export default {
  setByObject(instance: any, properties: object) {
    // for (const [key, value] of Object.entries(data)) {
    for (const [key, value] of Object.entries(properties)) {
      if (instance.hasOwnProperty(key)) {
        instance[key] = value;
      }
    }
  }
}