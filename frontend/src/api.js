async function request(path, options = {}) {
  const response = await fetch(`/api${path}`, {
    headers: {'Content-Type': 'application/json' },
    ...options
  });
  if (res.status === 204) return null
  const data = await res.json().catch(() => ({}))
  if (!res.ok) throw new Error(data.error || `Request failed (${res.status})`)
  return data
}

export const api = {
  list: () => request('/tasks'),
  create: (title) => request('/tasks', { method: 'POST', body: JSON.stringify({ title }) }),
  toggle: (id, completed) => request(`/tasks/${id}`, { method: 'PATCH', body: JSON.stringify({ completed }) }),
  remove: (id) => request(`/tasks/${id}`, { method: 'DELETE' }),
}