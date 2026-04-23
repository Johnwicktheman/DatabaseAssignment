    // Sample data – in a real app this would be rendered by PHP from MySQL
    var users = [
      { id: 'S10001', name: 'Ahmad Faris',     email: 'ahmad.faris@student.edu',   role: 'Student' },
      { id: 'S10002', name: 'Nur Hidayah',     email: 'nur.hidayah@student.edu',   role: 'Student' },
      { id: 'S10003', name: 'Raj Kumar',       email: 'raj.kumar@student.edu',     role: 'Student' },
      { id: 'S10004', name: 'Li Wei',          email: 'li.wei@student.edu',        role: 'Student' },
      { id: 'A00001', name: 'Admin User',      email: 'admin@university.edu',      role: 'Admin'   },
    ];

    var editingIndex = null;

    function renderTable(data) {
      var tbody = document.getElementById('tableBody');
      tbody.innerHTML = '';

      if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#999;padding:20px;">No records found.</td></tr>';
        return;
      }

      data.forEach(function(user, i) {
        var badgeClass = user.role === 'Admin' ? 'badge-admin' : 'badge-student';
        var row = '<tr>' +
          '<td>' + user.id + '</td>' +
          '<td>' + user.name + '</td>' +
          '<td>' + user.email + '</td>' +
          '<td><span class="badge ' + badgeClass + '">' + user.role + '</span></td>' +
          '<td><div class="action-btns">' +
            '<button class="btn-sm btn-edit" onclick="openModal(\'edit\',' + i + ')">Edit</button>' +
            '<button class="btn-sm btn-del"  onclick="deleteUser(' + i + ')">Delete</button>' +
          '</div></td>' +
        '</tr>';
        tbody.innerHTML += row;
      });
    }

    function filterTable() {
      var query = document.getElementById('searchInput').value.toLowerCase();
      var filtered = users.filter(function(u) {
        return u.id.toLowerCase().includes(query) || u.name.toLowerCase().includes(query);
      });
      renderTable(filtered);
    }

    function openModal(mode, index) {
      document.getElementById('modalOverlay').classList.add('active');
      editingIndex = null;

      if (mode === 'edit' && index !== undefined) {
        editingIndex = index;
        var u = users[index];
        document.getElementById('modalTitle').textContent = 'Edit Student';
        document.getElementById('mStudentId').value = u.id;
        document.getElementById('mName').value       = u.name;
        document.getElementById('mEmail').value      = u.email;
        document.getElementById('mRole').value       = u.role;
      } else {
        document.getElementById('modalTitle').textContent = 'Add Student';
        document.getElementById('mStudentId').value = '';
        document.getElementById('mName').value      = '';
        document.getElementById('mEmail').value     = '';
        document.getElementById('mRole').value      = 'Student';
      }
    }

    function closeModal() {
      document.getElementById('modalOverlay').classList.remove('active');
    }

    function saveUser() {
      var sid   = document.getElementById('mStudentId').value.trim();
      var name  = document.getElementById('mName').value.trim();
      var email = document.getElementById('mEmail').value.trim();
      var role  = document.getElementById('mRole').value;

      if (!sid || !name || !email) {
        alert('Please fill in all fields.');
        return;
      }

      if (editingIndex !== null) {
        // Edit existing
        users[editingIndex] = { id: sid, name: name, email: email, role: role };
      } else {
        // Add new
        users.push({ id: sid, name: name, email: email, role: role });
      }

      closeModal();
      renderTable(users);

      // TODO: In real app, send data via form POST or fetch() to save_user.php
    }

    function deleteUser(index) {
      if (confirm('Are you sure you want to delete this user?')) {
        users.splice(index, 1);
        renderTable(users);
        // TODO: In real app, send DELETE request to delete_user.php?id=...
      }
    }

    // Initial render
    renderTable(users);