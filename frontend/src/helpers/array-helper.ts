export default {
  remove(array: any[], index: number): void {
    array.splice(index, 1);
  },
  intersects(array1, array2) {
    return array1.some(element => array2.includes(element));
  },
}