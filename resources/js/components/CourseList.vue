<template>
  <div class="course-list">
    <div class="filters mb-4">
      <input v-model="search" placeholder="Search courses..." class="form-control mb-2">
      <select v-model="stateFilter" class="form-select">
        <option value="">All States</option>
        <option value="FL">Florida</option>
        <option value="CA">California</option>
      </select>
    </div>
    
    <div class="row">
      <div v-for="course in filteredCourses" :key="course.id" class="col-md-4 mb-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">{{ course.title }}</h5>
            <p class="card-text">{{ course.description }}</p>
            <p><strong>State:</strong> {{ course.state_code }}</p>
            <p><strong>Duration:</strong> {{ course.total_duration }} minutes</p>
            <p><strong>Price:</strong> ${{ course.price }}</p>
            <button @click="enrollCourse(course.id)" class="btn btn-primary">Enroll</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      courses: [],
      search: '',
      stateFilter: ''
    }
  },
  computed: {
    filteredCourses() {
      return this.courses.filter(course => {
        const matchesSearch = course.title.toLowerCase().includes(this.search.toLowerCase())
        const matchesState = !this.stateFilter || course.state_code === this.stateFilter
        return matchesSearch && matchesState && course.is_active
      })
    }
  },
  methods: {
    async fetchCourses() {
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        
        const headers = {
          'Accept': 'application/json'
        }
        
        if (csrfToken) {
          headers['X-CSRF-TOKEN'] = csrfToken
        }
        
        const response = await fetch('/web/courses', {
          headers: headers,
          credentials: 'same-origin'
        })
        
        if (!response.ok) {
          if (response.status === 401) {
            window.location.href = '/login'
            return
          }
          throw new Error('Failed to fetch courses')
        }
        
        this.courses = await response.json()
      } catch (error) {
        console.error('Error fetching courses:', error)
      }
    },
    async enrollCourse(courseId) {
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        
        const headers = {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
        
        if (csrfToken) {
          headers['X-CSRF-TOKEN'] = csrfToken
        }
        
        const response = await fetch('/web/enrollments', {
          method: 'POST',
          headers: headers,
          credentials: 'same-origin',
          body: JSON.stringify({ course_id: courseId })
        })
        
        if (!response.ok) {
          if (response.status === 401) {
            window.location.href = '/login'
            return
          }
          const data = await response.json()
          throw new Error(data.error || 'Failed to enroll')
        }
        
        alert('Enrolled successfully!')
      } catch (error) {
        console.error('Error enrolling:', error)
        alert(error.message || 'Failed to enroll in course')
      }
    }
  },
  mounted() {
    this.fetchCourses()
  }
}
</script>
