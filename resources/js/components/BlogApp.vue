<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

// ── State ──────────────────────────────────────────────
const posts        = ref([])
const view         = ref('list')   // 'list' | 'read' | 'form'
const selectedPost = ref(null)
const editingPost  = ref(null)
const filterStatus = ref('all')
const search       = ref('')

const form = ref({ title: '', category: '', author: '', body: '', status: 'draft' })

// ── Load ───────────────────────────────────────────────
onMounted(async () => {
  const res = await axios.get('/api/posts')
  posts.value = res.data
})

// ── Computed ───────────────────────────────────────────
const filteredPosts = computed(() => {
  return posts.value.filter(p => {
    const matchStatus = filterStatus.value === 'all' || p.status === filterStatus.value
    const matchSearch = p.title.toLowerCase().includes(search.value.toLowerCase()) ||
                        p.category.toLowerCase().includes(search.value.toLowerCase()) ||
                        p.author.toLowerCase().includes(search.value.toLowerCase())
    return matchStatus && matchSearch
  })
})

const publishedCount = computed(() => posts.value.filter(p => p.status === 'published').length)
const draftCount     = computed(() => posts.value.filter(p => p.status === 'draft').length)

// ── Navigation ─────────────────────────────────────────
const openRead = (post) => {
  selectedPost.value = post
  view.value = 'read'
}

const openAddForm = () => {
  editingPost.value = null
  form.value = { title: '', category: '', author: '', body: '', status: 'draft' }
  view.value = 'form'
}

const openEditForm = (post) => {
  editingPost.value = post
  form.value = { ...post }
  view.value = 'form'
}

const goBack = () => {
  view.value = 'list'
  selectedPost.value = null
  editingPost.value = null
}

// ── CRUD ───────────────────────────────────────────────
const savePost = async () => {
  if (!form.value.title || !form.value.category || !form.value.author || !form.value.body) return

  if (editingPost.value) {
    const res = await axios.put(`/api/posts/${editingPost.value.id}`, form.value)
    const idx = posts.value.findIndex(p => p.id === editingPost.value.id)
    posts.value[idx] = res.data
  } else {
    const res = await axios.post('/api/posts', form.value)
    posts.value.unshift(res.data)
  }

  goBack()
}

const deletePost = async (id) => {
  if (!confirm('Delete this post?')) return
  await axios.delete(`/api/posts/${id}`)
  posts.value = posts.value.filter(p => p.id !== id)
  if (view.value === 'read') goBack()
}

// ── Helpers ────────────────────────────────────────────
const formatDate = (d) => new Date(d).toLocaleDateString('en-US', {
  year: 'numeric', month: 'long', day: 'numeric'
})

const excerpt = (text, len = 120) =>
  text.length > len ? text.slice(0, len) + '…' : text
</script>

<template>
  <div class="blog-wrap">

    <!-- ── HEADER ── -->
    <header class="site-header">
      <div class="header-inner">
        <div class="brand">
          <span class="brand-icon">✍️</span>
          <span class="brand-name">The Blog</span>
        </div>
        <button class="btn-write" @click="openAddForm">+ New Post</button>
      </div>
    </header>

    <!-- ════════════════════════════════
         LIST VIEW
    ════════════════════════════════ -->
    <main v-if="view === 'list'" class="main-content">

      <!-- Stats -->
      <div class="stats-row">
        <div class="stat">
          <div class="stat-num">{{ posts.length }}</div>
          <div class="stat-lbl">Total Posts</div>
        </div>
        <div class="stat">
          <div class="stat-num published-num">{{ publishedCount }}</div>
          <div class="stat-lbl">Published</div>
        </div>
        <div class="stat">
          <div class="stat-num draft-num">{{ draftCount }}</div>
          <div class="stat-lbl">Drafts</div>
        </div>
      </div>

      <!-- Toolbar -->
      <div class="toolbar">
        <input class="search-box" v-model="search" placeholder="Search posts…" />
        <div class="filter-tabs">
          <button :class="['tab', { active: filterStatus === 'all' }]"       @click="filterStatus = 'all'">All</button>
          <button :class="['tab', { active: filterStatus === 'published' }]"  @click="filterStatus = 'published'">Published</button>
          <button :class="['tab', { active: filterStatus === 'draft' }]"      @click="filterStatus = 'draft'">Drafts</button>
        </div>
      </div>

      <!-- Post Cards -->
      <div class="post-grid">
        <article
          v-for="post in filteredPosts"
          :key="post.id"
          class="post-card"
        >
          <div class="card-top">
            <span class="category-tag">{{ post.category }}</span>
            <span :class="['status-pill', post.status]">{{ post.status }}</span>
          </div>
          <h2 class="card-title" @click="openRead(post)">{{ post.title }}</h2>
          <p class="card-excerpt">{{ excerpt(post.body) }}</p>
          <div class="card-footer">
            <div class="card-meta">
              <span class="author">✍ {{ post.author }}</span>
              <span class="date">{{ formatDate(post.created_at) }}</span>
            </div>
            <div class="card-actions">
              <button class="act-btn read-btn"   @click="openRead(post)">Read</button>
              <button class="act-btn edit-btn"   @click="openEditForm(post)">Edit</button>
              <button class="act-btn delete-btn" @click="deletePost(post.id)">Delete</button>
            </div>
          </div>
        </article>

        <div v-if="filteredPosts.length === 0" class="empty-state">
          <p>📭 No posts found.</p>
        </div>
      </div>
    </main>

    <!-- ════════════════════════════════
         READ VIEW
    ════════════════════════════════ -->
    <main v-if="view === 'read' && selectedPost" class="main-content read-view">
      <button class="back-btn" @click="goBack">← Back to Posts</button>

      <article class="full-post">
        <div class="post-meta-top">
          <span class="category-tag">{{ selectedPost.category }}</span>
          <span :class="['status-pill', selectedPost.status]">{{ selectedPost.status }}</span>
        </div>
        <h1 class="post-title">{{ selectedPost.title }}</h1>
        <div class="post-byline">
          <span>✍ {{ selectedPost.author }}</span>
          <span>{{ formatDate(selectedPost.created_at) }}</span>
        </div>
        <div class="post-divider"></div>
        <div class="post-body">{{ selectedPost.body }}</div>
        <div class="post-actions">
          <button class="act-btn edit-btn"   @click="openEditForm(selectedPost)">Edit Post</button>
          <button class="act-btn delete-btn" @click="deletePost(selectedPost.id)">Delete Post</button>
        </div>
      </article>
    </main>

    <!-- ════════════════════════════════
         FORM VIEW (Add / Edit)
    ════════════════════════════════ -->
    <main v-if="view === 'form'" class="main-content form-view">
      <button class="back-btn" @click="goBack">← Back to Posts</button>

      <div class="form-card">
        <h2 class="form-title">{{ editingPost ? '✏️ Edit Post' : '✍️ New Post' }}</h2>

        <div class="form-row two-col">
          <div class="form-group">
            <label>Title</label>
            <input v-model="form.title" placeholder="Post title…" />
          </div>
          <div class="form-group">
            <label>Author</label>
            <input v-model="form.author" placeholder="Author name…" />
          </div>
        </div>

        <div class="form-row two-col">
          <div class="form-group">
            <label>Category</label>
            <input v-model="form.category" placeholder="e.g. Technology, Tutorial…" />
          </div>
          <div class="form-group">
            <label>Status</label>
            <select v-model="form.status">
              <option value="draft">Draft</option>
              <option value="published">Published</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label>Body</label>
          <textarea v-model="form.body" placeholder="Write your post content here…" rows="10"></textarea>
        </div>

        <div class="form-footer">
          <button class="btn-cancel" @click="goBack">Cancel</button>
          <button class="btn-save"   @click="savePost">
            {{ editingPost ? 'Update Post' : 'Publish Post' }}
          </button>
        </div>
      </div>
    </main>

  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Source+Serif+4:ital,wght@0,300;0,400;1,300&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }

.blog-wrap {
  min-height: 100vh;
  background: #faf8f3;
  font-family: 'Source Serif 4', Georgia, serif;
  color: #1c1410;
}

/* ── HEADER ── */
.site-header {
  background: #1c1410;
  color: #faf8f3;
  padding: 0 32px;
  position: sticky;
  top: 0;
  z-index: 50;
  box-shadow: 0 2px 16px rgba(0,0,0,0.3);
}

.header-inner {
  max-width: 1000px;
  margin: 0 auto;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.brand { display: flex; align-items: center; gap: 10px; }
.brand-icon { font-size: 1.4rem; }
.brand-name {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: 0.02em;
}

.btn-write {
  background: #c8913a;
  color: white;
  border: none;
  padding: 9px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-family: 'Source Serif 4', serif;
  font-weight: 400;
  font-size: 0.95rem;
  transition: background 0.2s;
}
.btn-write:hover { background: #a97528; }

/* ── MAIN ── */
.main-content {
  max-width: 1000px;
  margin: 0 auto;
  padding: 40px 24px 80px;
}

/* ── STATS ── */
.stats-row {
  display: flex;
  gap: 20px;
  margin-bottom: 32px;
}

.stat {
  background: white;
  border: 1px solid #e8e0d0;
  border-radius: 10px;
  padding: 18px 28px;
  text-align: center;
  flex: 1;
}
.stat-num { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: #1c1410; }
.published-num { color: #2d7a4f; }
.draft-num     { color: #c8913a; }
.stat-lbl { font-size: 0.8rem; color: #888; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.08em; }

/* ── TOOLBAR ── */
.toolbar {
  display: flex;
  gap: 16px;
  align-items: center;
  margin-bottom: 28px;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 200px;
  padding: 10px 14px;
  border: 1px solid #ddd5c5;
  border-radius: 8px;
  font-family: 'Source Serif 4', serif;
  font-size: 0.95rem;
  background: white;
}

.filter-tabs { display: flex; gap: 6px; }
.tab {
  padding: 8px 18px;
  border: 1px solid #ddd5c5;
  border-radius: 20px;
  background: white;
  cursor: pointer;
  font-family: 'Source Serif 4', serif;
  font-size: 0.875rem;
  color: #666;
  transition: all 0.2s;
}
.tab.active { background: #1c1410; color: white; border-color: #1c1410; }

/* ── POST GRID ── */
.post-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 24px;
}

.post-card {
  background: white;
  border: 1px solid #e8e0d0;
  border-radius: 12px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  transition: box-shadow 0.2s, transform 0.2s;
}
.post-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.1); transform: translateY(-2px); }

.card-top { display: flex; justify-content: space-between; align-items: center; }

.category-tag {
  background: #f0e8d8;
  color: #8b5e1a;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.status-pill {
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}
.status-pill.published { background: #d1fae5; color: #065f46; }
.status-pill.draft     { background: #fef3c7; color: #92400e; }

.card-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.2rem;
  line-height: 1.4;
  cursor: pointer;
  color: #1c1410;
}
.card-title:hover { color: #c8913a; }

.card-excerpt { font-size: 0.9rem; color: #666; line-height: 1.6; flex: 1; font-style: italic; }

.card-footer { display: flex; flex-direction: column; gap: 10px; margin-top: auto; }

.card-meta { display: flex; flex-direction: column; gap: 2px; }
.author { font-size: 0.82rem; color: #555; }
.date   { font-size: 0.78rem; color: #aaa; }

.card-actions { display: flex; gap: 8px; }

.act-btn {
  padding: 5px 14px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-family: 'Source Serif 4', serif;
  font-size: 0.82rem;
  font-weight: 400;
  transition: opacity 0.2s;
}
.act-btn:hover { opacity: 0.8; }

.read-btn   { background: #1c1410; color: white; }
.edit-btn   { background: #e0e7ff; color: #3730a3; }
.delete-btn { background: #fee2e2; color: #991b1b; }

.empty-state { grid-column: 1/-1; text-align: center; color: #aaa; padding: 60px 0; font-size: 1.1rem; }

/* ── READ VIEW ── */
.back-btn {
  background: none;
  border: none;
  color: #c8913a;
  cursor: pointer;
  font-family: 'Source Serif 4', serif;
  font-size: 0.95rem;
  margin-bottom: 32px;
  padding: 0;
}
.back-btn:hover { text-decoration: underline; }

.full-post { background: white; border: 1px solid #e8e0d0; border-radius: 16px; padding: 48px; }

.post-meta-top { display: flex; gap: 10px; margin-bottom: 20px; }

.post-title {
  font-family: 'Playfair Display', serif;
  font-size: 2.2rem;
  line-height: 1.3;
  margin-bottom: 16px;
  color: #1c1410;
}

.post-byline {
  display: flex;
  gap: 24px;
  color: #888;
  font-size: 0.9rem;
  margin-bottom: 24px;
  font-style: italic;
}

.post-divider {
  height: 1px;
  background: #e8e0d0;
  margin-bottom: 28px;
}

.post-body {
  font-size: 1.05rem;
  line-height: 1.9;
  color: #2a2018;
  white-space: pre-wrap;
  margin-bottom: 40px;
}

.post-actions { display: flex; gap: 12px; }

/* ── FORM VIEW ── */
.form-card {
  background: white;
  border: 1px solid #e8e0d0;
  border-radius: 16px;
  padding: 40px;
}

.form-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.6rem;
  margin-bottom: 28px;
  color: #1c1410;
}

.form-row { margin-bottom: 20px; }
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

.form-group { display: flex; flex-direction: column; gap: 6px; }

.form-group label {
  font-size: 0.82rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: #555;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 10px 14px;
  border: 1px solid #ddd5c5;
  border-radius: 8px;
  font-family: 'Source Serif 4', serif;
  font-size: 0.95rem;
  background: #faf8f3;
  color: #1c1410;
  transition: border-color 0.2s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #c8913a;
}

.form-group textarea { resize: vertical; line-height: 1.7; }

.form-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 28px;
  padding-top: 24px;
  border-top: 1px solid #e8e0d0;
}

.btn-cancel {
  padding: 10px 22px;
  border: 1px solid #ddd5c5;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  font-family: 'Source Serif 4', serif;
  font-size: 0.95rem;
  color: #555;
}

.btn-save {
  padding: 10px 28px;
  border: none;
  border-radius: 8px;
  background: #1c1410;
  color: white;
  cursor: pointer;
  font-family: 'Source Serif 4', serif;
  font-size: 0.95rem;
  transition: background 0.2s;
}
.btn-save:hover { background: #c8913a; }
</style>