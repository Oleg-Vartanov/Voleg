import axios from 'axios';

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL;
// const apiVersion = 'v1';

const authType = 'Bearer';
const accessToken = 'value123';
const headers = {Authorization: `${authType} ${accessToken}`,};

export default {
  signIn(params: object): Promise<axios.AxiosResponse> {
    return axios.post(`${apiBaseUrl}/auth/sign-in`, params);
  },

  signUp(params: object) {
    return axios.post(`${apiBaseUrl}/auth/sign-up`, params);
  },

  // test() {
  //   return axios.get(`${apiBaseUrl}/auth/test`, { headers: headers });
  // },
};
