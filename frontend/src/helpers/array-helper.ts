export default {
  removeIndex(array: any[], index: number): void {
    array.splice(index, 1);
  },
  removeItem(array: any[], item: any): void {
    if (array.includes(item)) {
      array.splice(array.indexOf(item), 1);
    }
  },
  intersects(array1, array2) {
    return array1.some(element => array2.includes(element));
  },
}