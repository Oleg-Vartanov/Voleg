import type { Router } from 'vue-router';

import {useRouter} from 'vue-router';

export default {
  url(routeName): string {
    const router: Router = useRouter();
    const protocol: string = window.location.protocol;
    const host: string = window.location.host;
    const path: string = router.resolve({ name: routeName }).fullPath;

    return `${protocol}//${host}${path}`;
  }
}