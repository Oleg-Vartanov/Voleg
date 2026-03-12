import { ref } from 'vue';
import Client from '@/modules/core/apiClient';
import arrayUtils from '@/modules/core/utils/arrayUtils';

export interface HeadToHead {
  searchUsers: Ref<any[]>;
  users: Ref<any[]>;
  input: Ref<{ value: string; error: string }>;
  searchUser: () => Promise<void>;
  addUser: (user: any) => void;
  removeUser: (user: any) => void;
}

const isLoading = ref(false);
const searchUsers = ref([]);
const users = ref([]);
const input = ref({ value: '', error: '' });

export function useHeadToHead(): HeadToHead {
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
    if (!users.value.includes(user)) {
      users.value.push(user);
    }
  }

  function removeUser(user: any) {
    arrayUtils.removeItem(users.value, user);
  }

  return { isLoading, searchUsers, users, input, searchUser, addUser, removeUser };
}
