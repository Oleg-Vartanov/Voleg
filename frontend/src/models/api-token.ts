export class ApiToken {
  public value: string;
  public expiresAtTimestamp: number;
  public type: string;

  public constructor(
    value: string = '',
    expiresAtTimestamp: number = 0,
    type: string = 'Bearer'
  ) {
    this.value = value;
    this.expiresAtTimestamp = expiresAtTimestamp;
    this.type = type;
  }
}