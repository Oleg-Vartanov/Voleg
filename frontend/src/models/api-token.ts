import ClassHelper from '@/helpers/class-helper';

export class ApiToken {
  public value = '';
  public expiresAtTimestamp = 0;
  public type = 'Bearer';

  public setByObject(properties: object) {
    ClassHelper.setByObject(this, properties)
  }
}