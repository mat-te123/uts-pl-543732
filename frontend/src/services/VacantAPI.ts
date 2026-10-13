const base_url = import.meta.env.VITE_API_URL 

export const VacantAPI = {
  getindex: async () => {
    try {
      const response = await fetch(`${base_url}/vacants`)

      if (!response.ok) {
        throw new Error(`HTTP ERROR! STATUS ${response.status}`)
      }

      const data = await response.json()

      return data.vacants
    } catch (error) {
      return error
    }
  },
  addvacant: async (vacant_name: string, vacant_desc: string, vacant_link: string) => {
    try {
      const response = await fetch(`${base_url}/vacant/post`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          vacant_name,
          vacant_desc,
          vacant_link,
        }),
      })

      if (!response.ok) {
        throw new Error(`HTTP ERROR! STATUS ${response.status}`)
      }

      const data = await response.json()

      return data.Status
    } catch (error) {
      return error
    }
  },
}
