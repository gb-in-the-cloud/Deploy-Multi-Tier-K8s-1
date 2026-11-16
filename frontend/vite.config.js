import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  server: {
    // During `npm run dev`, forward /api calls to the PHP container
    proxy: { '/api': 'http://localhost:8000' },
  },
})
