import { useEffect, useState } from 'react'
import { api } from './api.js'

export default function App() {
  const [tasks, setTasks] = useState([])
  const [title, setTitle] = useState('')
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    api.list()
      .then(setTasks)
      .catch((e) => setError(e.message))
      .finally(() => setLoading(false))
  }, [])

  async function addTask(e) {
    e.preventDefault()
    if (!title.trim()) return
    try {
      const task = await api.create(title.trim())
      setTasks([task, ...tasks])
      setTitle('')
      setError('')
    } catch (e) {
      setError(e.message)
    }
  }

  async function toggleTask(task) {
    try {
      const updated = await api.toggle(task.id, !task.completed)
      setTasks(tasks.map((t) => (t.id === task.id ? updated : t)))
    } catch (e) {
      setError(e.message)
    }
  }

  async function deleteTask(id) {
    try {
      await api.remove(id)
      setTasks(tasks.filter((t) => t.id !== id))
    } catch (e) {
      setError(e.message)
    }
  }

  const remaining = tasks.filter((t) => !t.completed).length

  return (
    <main className="container">
      <h1>Task Tracker</h1>
      <p className="subtitle">React · PHP · PostgreSQL</p>

      <form onSubmit={addTask} className="add-form">
        <input
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          placeholder="What needs doing?"
          maxLength={255}
          aria-label="New task"
        />
        <button type="submit">Add</button>
      </form>

      {error && <p className="error" role="alert">{error}</p>}

      {loading ? (
        <p className="muted">Loading…</p>
      ) : tasks.length === 0 ? (
        <p className="muted">No tasks yet. Add one above.</p>
      ) : (
        <>
          <ul className="task-list">
            {tasks.map((task) => (
              <li key={task.id} className={task.completed ? 'done' : ''}>
                <label>
                  <input type="checkbox" checked={task.completed} onChange={() => toggleTask(task)} />
                  <span>{task.title}</span>
                </label>
                <button className="delete" onClick={() => deleteTask(task.id)} aria-label={`Delete ${task.title}`}>
                  ×
                </button>
              </li>
            ))}
          </ul>
          <p className="muted">{remaining} of {tasks.length} remaining</p>
        </>
      )}
    </main>
  )
}
