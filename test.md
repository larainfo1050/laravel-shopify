# 1. CREATE
mutation {
  createTask(name: "Test Task 1", completed: false) {
    id name completed
  }
}

# 2. READ ALL
query {
  tasks { data { id name completed } }
}

# 3. READ ONE
query {
  task(id: 1) { id name completed }
}

# 4. UPDATE
mutation {
  updateTask(id: 1, completed: true) {
    id name completed
  }
}

# 5. DELETE
mutation {
  deleteTask(id: 1) { id name }
}