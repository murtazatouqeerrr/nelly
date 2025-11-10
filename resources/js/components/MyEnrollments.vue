<template>
  <div class="my-enrollments">
    <h2>My Course Enrollments</h2>
    <div v-if="enrollments.length === 0" class="text-center py-4">
      <p>No enrollments found. <a href="/courses">Browse courses</a> to get started.</p>
    </div>
    <div v-else class="row">
      <div v-for="enrollment in enrollments" :key="enrollment.id" class="col-md-6 mb-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">{{ enrollment.course.title }}</h5>
            <p class="card-text">{{ enrollment.course.description }}</p>
            <div class="progress mb-2">
              <div class="progress-bar" :style="{width: enrollment.progress_percentage + '%'}">
                {{ enrollment.progress_percentage }}%
              </div>
            </div>
            <p><strong>Status:</strong> {{ enrollment.status }}</p>
            <p><strong>Payment:</strong> {{ enrollment.payment_status }}</p>
            <a :href="`/course-player?enrollmentId=${enrollment.id}`" class="btn btn-primary">Continue Course</a>
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
      enrollments: []
    }
  },
  methods: {
    async fetchEnrollments() {
      try {
        const response = await fetch('/web/my-enrollments', {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          credentials: 'same-origin'
        })
        
        if (!response.ok) {
          if (response.status === 401) {
            window.location.href = '/login'
            return
          }
          throw new Error('Failed to fetch enrollments')
        }
        
        this.enrollments = await response.json()
      } catch (error) {
        console.error('Error fetching enrollments:', error)
      }
    }
  },
  mounted() {
    this.fetchEnrollments()
  }
}
</script>
