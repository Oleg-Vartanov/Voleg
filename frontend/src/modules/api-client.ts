import axios from 'axios';
import {useAuth} from "@/modules/auth";

const auth = useAuth();

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL;
const authType: string = 'Bearer';

const getHeader = () => {
  return {Authorization: `${authType} ${auth.getToken()}`};
}

export default {
  signIn(params: object): Promise<axios.AxiosResponse> {
    return axios.post(`${apiBaseUrl}/auth/sign-in`, params);
  },

  signUp(params: object) {
    return axios.post(`${apiBaseUrl}/auth/sign-up`, params);
  },

  test() {
    return axios.get(`${apiBaseUrl}/test`, { headers: getHeader() });
  },
};
