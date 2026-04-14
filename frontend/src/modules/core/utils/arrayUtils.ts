export default {
  removeIndex<T>(array: T[], index: number): void {
    array.splice(index, 1);
  },
  removeItem<T>(array: T[], item: T): void {
    if (array.includes(item)) {
      array.splice(array.indexOf(item), 1);
    }
  },
  intersects<T>(array1: T[], array2: T[]) {
    return array1.some(element => array2.includes(element));
  },
  range(start: number, stop: number, step: number = 1): number[] {
    return Array.from(
      { length: Math.ceil((stop - start) / step) },
      (_, i) => start + i * step,
    );
  },
};