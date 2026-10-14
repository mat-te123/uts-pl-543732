import axios from 'axios'
import { TokenService } from './TokenService'

const base_url = import.meta.env.VITE_API_URL

export const AuthAPI = {
  register: async (data: { name: string; email: string; password: string }) => {
    try {
      const response = await axios.post(`${base_url}/user/register`, data)
      if (!response.data.message) {
        throw new Error(response.data.message)
      }
      return response.data
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Registration failed')
    }
  },
  login: async (data: { email: string; password: string }) => {
    try {
      const response = await axios.post(`${base_url}/user/login`, data)
      if (response.data.message == 'Unauthorize') {
        throw new Error('Invalid email or password')
      }
      TokenService.createToken(response.data.access_token)
      return response.data
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Login failed')
    }
  },
  logout: async () => {
    try {
      const response = await axios.post(`${base_url}/user/logout`)
      if (!response.data.success) {
        throw new Error(response.data.message)
      }
      TokenService.removeToken()
      return response.data
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Logout failed')
    }
  },
}
