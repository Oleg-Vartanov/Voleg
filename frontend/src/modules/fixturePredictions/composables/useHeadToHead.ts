import { type Ref, ref } from 'vue';
import { useAuth } from '@/modules/user/stores/useAuth';
import Client from '@/modules/core/apiClient';
import arrayUtils from '@/modules/core/utils/arrayUtils';
import { useRoute, useRouter } from 'vue-router';

export interface HeadToHead {
  searchUsers: Ref<any[]>;
  users: Ref<any[]>;
  input: Ref<{ value: string; error: string }>;
  searchUser: () => Promise<void>;
  isLoading: Ref<boolean>;
  addUser: (user: any) => void;
  removeUser: (user: any) => void;
  updateByResponse: (response: any) => void;
  routeQuery: (response: any) => object;
  getUserIds: () => number[];
}

export function useHeadToHead(): HeadToHead {
  const route = useRoute();
  const router = useRouter();
  const auth = useAuth();

  const wasRequested = ref(false);
  const isLoading = ref(false);
  const searchUsers = ref([]);
  const users = ref([]);
  const input = ref({ value: '', error: '' });

  async function searchUser() {
    isLoading.value = true;
    try {
      const response = await Client.listUsers(input.value.value);
      input.value.error = '';
      searchUsers.value = response.data;
    } catch {
      input.value.error = 'Unexpected error :(';
    } finally {
      isLoading.value = false;
    }
  }

  function addUser(user: any) {
    if (!users.value.includes(user) && auth.user.id !== user.id) {
      users.value.push(user);
    }
  }

  function removeUser(user: any) {
    arrayUtils.removeItem(users.value, user);

    router.replace({
      query: {
        ...route.query,
        ...routeQuery(),
      },
    });
  }

  function updateByResponse(response: any): void {
    wasRequested.value = true;
    users.value = response.data.filters.users.filter(function(user) {
      return user.id !== auth.user.id;
    });
  }

  function routeQuery() {
    return {
      userIds: users.value.length > 0
        ? users.value.map(u => u.id).join(',')
        : undefined,
    }
  }

  function getUserIds(): number[] {
    const queryIds = route.query.userIds
      ? route.query.userIds
        .split(',')
        .map(s => Number(s.trim()))
        .filter(n => !isNaN(n))
      : [];
    const filterIds = users.value.map(u => u.id);

    return wasRequested.value ? filterIds : queryIds;
  }

  return {
    isLoading,
    searchUsers,
    users,
    input,
    searchUser,
    addUser,
    removeUser,
    updateByResponse,
    routeQuery,
    getUserIds,
  };
}
