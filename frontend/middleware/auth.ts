export default defineNuxtRouteMiddleware(async (to) => {
  if (to.path === '/login' || to.path === '/register') {
    return
  }

  const authStore = useAuthStore()

  if (!authStore.initialized) {
    try {
      await authStore.initializeAuth()
    } catch {
      // The protected route should fall through to the login redirect below.
    }
  }

  if (!authStore.isAuthenticated) {
    return navigateTo('/login')
  }
})
