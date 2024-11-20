import axios from 'axios';
import {useAuth} from "@/modules/auth";

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL;
const authType: string = 'Bearer';

const getHeader = () => {
  const auth = useAuth();
  return auth.user.isSignedIn ? {Authorization: `${authType} ${auth.getToken()}`} : {};
}

export default {
  signIn(params: object): Promise<axios.AxiosResponse> {
    return axios.post(`${apiBaseUrl}/auth/sign-in`, params);
  },

  signUp(params: object) {
    return axios.post(`${apiBaseUrl}/auth/sign-up`, params);
  },

  syncFixtures(competitionCode: string, seasonYear: number) {
    return axios.post(
      `${apiBaseUrl}/fixtures/sync`,
      { competitionCode, seasonYear },
      { headers: getHeader() }
    );
  },

  showFixtures() {
    return axios.get(`${apiBaseUrl}/fixtures/predictions`, { headers: getHeader() });
  },
};
