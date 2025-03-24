import type {Ref, UnwrapRef} from "vue";
import {ref} from "vue";

export class Alert {
  public id: number = 0;
  public text: string;
  public type: string;
  public timeout: number; // Seconds.
  public countdown: Ref<UnwrapRef<number>> = ref(0) // Seconds.

  public constructor(text: string, type: string = 'primary', timeout: number = 0) {
    this.text = text;
    this.type = type;
    this.timeout = timeout;

    if (this.timeout > 0) {
      this.countdown.value = this.timeout;
      this.countdownTick();
    }
  }

  // countdown -1 each second.
  private countdownTick(): void {
    setTimeout((): void => {
      if (this.countdown.value > 0) {
        this.countdown.value -= 1;
        this.countdownTick();
      }
    }, 1000)
  }
}