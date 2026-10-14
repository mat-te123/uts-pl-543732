import apiClient from './api/api'

const base_url = import.meta.env.VITE_API_URL

export const VacantAPI = {
  getindex: async () => {
    try {
      const response = await apiClient.get(`${base_url}/vacants`)

      if (!response.data) {
        throw new Error(`HTTP ERROR! STATUS ${response.status}`)
      }

      return response.data
    } catch (error) {
      return error
    }
  },
  addvacant: async (data: { vacant_name: string; vacant_desc: string; vacant_link: string }) => {
    try {
      const response = await apiClient.post('/vacant/post', {
        vacant_name: data.vacant_name,
        vacant_desc: data.vacant_desc,
        vacant_link: data.vacant_link,
      })
      
      return response.data
    } catch (error: any) {
      throw error.response?.data || error
    }
  },
}
