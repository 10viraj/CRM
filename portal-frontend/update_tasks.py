import re
import os

app_jsx_path = r"c:\Users\viraj\CRM\portal-frontend\src\App.jsx"

with open(app_jsx_path, 'r', encoding='utf-8') as f:
    content = f.read()

new_dashboard_tasks = """// 8. Dashboard Tasks (New Feature)
function DashboardTasks() {
  const [activeTab, setActiveTab] = useState('All');
  const [tasks, setTasks] = useState([]);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingTask, setEditingTask] = useState(null);
  const [formData, setFormData] = useState({
    title: '', type: 'Call', priority: 'Medium', due_date: '', status: 'Pending', assign_to_id: ''
  });
  const [openDropdownId, setOpenDropdownId] = useState(null);

  const tabs = ['All', 'Pending', 'In Progress', 'Completed', 'Overdue'];

  const fetchTasks = async () => {
    try {
      const response = await axios.get(`http://127.0.0.1:8000/api/tasks?status=${activeTab}`, {
        headers: { Authorization: `Bearer ${localStorage.getItem('crm_token')}` }
      });
      setTasks(response.data.data);
    } catch (error) {
      console.error("Error fetching tasks", error);
    }
  };

  useEffect(() => {
    fetchTasks();
  }, [activeTab]);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleOpenModal = (task = null) => {
    if (task) {
      setEditingTask(task);
      setFormData({
        title: task.title, type: task.type, priority: task.priority, 
        due_date: task.due_date, status: task.status, assign_to_id: task.assign_to_id || ''
      });
    } else {
      setEditingTask(null);
      setFormData({
        title: '', type: 'Call', priority: 'Medium', due_date: '', status: 'Pending', assign_to_id: ''
      });
    }
    setIsModalOpen(true);
    setOpenDropdownId(null);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setEditingTask(null);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const headers = { Authorization: `Bearer ${localStorage.getItem('crm_token')}` };
      if (editingTask) {
        await axios.put(`http://127.0.0.1:8000/api/tasks/${editingTask.id}`, formData, { headers });
      } else {
        await axios.post(`http://127.0.0.1:8000/api/tasks`, formData, { headers });
      }
      handleCloseModal();
      fetchTasks();
    } catch (error) {
      console.error("Error saving task", error);
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm("Are you sure you want to delete this task?")) {
      try {
        await axios.delete(`http://127.0.0.1:8000/api/tasks/${id}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('crm_token')}` }
        });
        fetchTasks();
      } catch (error) {
        console.error("Error deleting task", error);
      }
    }
    setOpenDropdownId(null);
  };

  const toggleDropdown = (id) => {
    if (openDropdownId === id) setOpenDropdownId(null);
    else setOpenDropdownId(id);
  };

  const getPriorityBadge = (priority) => {
    if (priority === 'High') return 'bg-red-50 text-red-600 border border-red-200';
    if (priority === 'Medium') return 'bg-orange-50 text-orange-600 border border-orange-200';
    if (priority === 'Low') return 'bg-green-50 text-green-600 border border-green-200';
    return 'bg-gray-50 text-gray-600 border border-gray-200';
  };

  const getStatusBadge = (status) => {
    if (status === 'Pending') return 'bg-orange-50 text-orange-600 border-orange-200';
    if (status === 'In Progress') return 'bg-blue-100 text-blue-700 border-blue-200';
    if (status === 'Completed') return 'bg-green-100 text-green-700 border-green-200';
    return 'bg-gray-100 text-gray-700 border-gray-200';
  };

  return (
    <DashboardLayout title="Tasks">
      <div className="p-6">
        <div className="flex justify-between items-center mb-6">
          <div className="flex space-x-6 border-b border-gray-200 w-full overflow-x-auto">
            {tabs.map(tab => (
              <button 
                key={tab} 
                onClick={() => setActiveTab(tab)}
                className={`py-2 px-1 text-sm font-medium border-b-2 transition-colors whitespace-nowrap ${activeTab === tab ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}`}
              >
                {tab}
              </button>
            ))}
          </div>
          <div className="flex items-center space-x-3 ml-4 flex-shrink-0">
            <button onClick={() => handleOpenModal()} className="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition flex items-center shadow-sm">
              + Add Task
            </button>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-visible min-h-[400px]">
          <table className="w-full text-left text-sm">
            <thead className="bg-white border-b border-gray-200 text-gray-500 font-semibold">
              <tr>
                <th className="px-6 py-4">Task <span className="text-gray-400 text-xs ml-1">v</span></th>
                <th className="px-6 py-4">Related To <span className="text-gray-400 text-xs ml-1">v</span></th>
                <th className="px-6 py-4">Type</th>
                <th className="px-6 py-4">Priority</th>
                <th className="px-6 py-4">Due Date <span className="text-gray-400 text-xs ml-1">v</span></th>
                <th className="px-6 py-4">Assign To</th>
                <th className="px-6 py-4">Status <span className="text-gray-400 text-xs ml-1">v</span></th>
                <th className="px-6 py-4 text-center">Action</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {tasks.length === 0 ? (
                <tr>
                  <td colSpan="8" className="px-6 py-12 text-center text-gray-500">No tasks found. Click '+ Add Task' to create one.</td>
                </tr>
              ) : tasks.map(task => (
                <tr key={task.id} className="hover:bg-slate-50 transition">
                  <td className="px-6 py-4 font-medium text-gray-900">{task.title}</td>
                  <td className="px-6 py-4">
                    {task.related_to ? (
                      <>
                        <span className="text-blue-600 hover:underline cursor-pointer">{task.related_to.name || 'Related'}</span>
                        <span className="text-gray-400 ml-1">({task.related_to_type.split('\\').pop()})</span>
                      </>
                    ) : <span className="text-gray-400">-</span>}
                  </td>
                  <td className="px-6 py-4 text-gray-600">{task.type}</td>
                  <td className="px-6 py-4">
                    <span className={`inline-flex px-3 py-1 rounded-full text-xs font-semibold ${getPriorityBadge(task.priority)}`}>
                      {task.priority}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-gray-600">{task.due_date || '-'}</td>
                  <td className="px-6 py-4">
                    {task.assignee ? (
                      <div className="flex items-center space-x-2">
                        <div className="w-6 h-6 rounded-full bg-gray-200 overflow-hidden border border-gray-300 flex items-center justify-center text-xs font-bold text-indigo-600">
                          {task.assignee.name.charAt(0)}
                        </div>
                        <span className="text-gray-700 font-medium">{task.assignee.name}</span>
                      </div>
                    ) : <span className="text-gray-400">Unassigned</span>}
                  </td>
                  <td className="px-6 py-4">
                    <span className={`inline-flex px-3 py-1 border rounded-lg text-xs font-semibold ${getStatusBadge(task.status)}`}>
                      {task.status}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-center relative">
                    <button onClick={() => toggleDropdown(task.id)} className="text-gray-400 hover:text-gray-700 text-lg font-bold px-2 py-1">⋮</button>
                    {openDropdownId === task.id && (
                      <div className="absolute right-0 mt-2 w-32 bg-white rounded-md shadow-lg z-50 border border-gray-100 overflow-hidden">
                        <button onClick={() => handleOpenModal(task)} className="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Edit</button>
                        <button onClick={() => handleDelete(task.id)} className="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete</button>
                      </div>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {isModalOpen && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <h2 className="text-xl font-bold text-gray-900 mb-4">{editingTask ? 'Edit Task' : 'Add New Task'}</h2>
            <form onSubmit={handleSubmit}>
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" value={formData.title} onChange={handleInputChange} required className="w-full border border-gray-300 rounded-lg px-3 py-2 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
              </div>
              <div className="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Type</label>
                  <select name="type" value={formData.type} onChange={handleInputChange} className="w-full border border-gray-300 rounded-lg px-3 py-2 outline-none focus:border-blue-500">
                    <option>Call</option><option>Email</option><option>Meeting</option><option>Review</option><option>Other</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                  <select name="priority" value={formData.priority} onChange={handleInputChange} className="w-full border border-gray-300 rounded-lg px-3 py-2 outline-none focus:border-blue-500">
                    <option>High</option><option>Medium</option><option>Low</option>
                  </select>
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <select name="status" value={formData.status} onChange={handleInputChange} className="w-full border border-gray-300 rounded-lg px-3 py-2 outline-none focus:border-blue-500">
                    <option>Pending</option><option>In Progress</option><option>Completed</option><option>Overdue</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                  <input type="date" name="due_date" value={formData.due_date} onChange={handleInputChange} className="w-full border border-gray-300 rounded-lg px-3 py-2 outline-none focus:border-blue-500" />
                </div>
              </div>
              <div className="flex justify-end space-x-3 mt-6">
                <button type="button" onClick={handleCloseModal} className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">{editingTask ? 'Save Changes' : 'Create Task'}</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </DashboardLayout>
  );
}
"""

pattern = re.compile(r'// 8\. Dashboard Tasks \(New Feature\)\nfunction DashboardTasks\(\) \{.*?(?=// --- MAIN ROUTER ---)', re.DOTALL)
new_content = pattern.sub(new_dashboard_tasks + '\n', content)

with open(app_jsx_path, 'w', encoding='utf-8') as f:
    f.write(new_content)
print("Updated successfully")
