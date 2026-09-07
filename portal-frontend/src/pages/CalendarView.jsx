import React, { useState, useEffect, useCallback } from 'react';
import { api } from '../services/api';
import CalendarEventModal from '../components/CalendarEventModal';

export default function CalendarView() {
  const [currentDate, setCurrentDate] = useState(new Date());
  const [viewMode, setViewMode] = useState('Month'); // 'Month', 'Week', 'Day'
  const [events, setEvents] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedEventType, setSelectedEventType] = useState('All');
  const [selectedUserId, setSelectedUserId] = useState('');
  
  // Metadata
  const [stats, setStats] = useState({
    total_events: 0,
    upcoming_events: 0,
    today_events: 0,
  });
  const [usersList, setUsersList] = useState([]);

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingEvent, setEditingEvent] = useState(null);
  const [modalInitialDate, setModalInitialDate] = useState(null);
  const [selectedEventDetails, setSelectedEventDetails] = useState(null);

  // Fetch metadata
  const fetchMetadata = async () => {
    try {
      const res = await api.calendar.metadata();
      if (res.data?.stats) {
        setStats(res.data.stats);
      }
      if (res.data?.options?.users) {
        setUsersList(res.data.options.users);
      }
    } catch (err) {
      console.error('Failed to fetch calendar metadata:', err);
    }
  };

  // Fetch events based on current view range
  const fetchEvents = useCallback(async () => {
    try {
      setLoading(true);
      const params = {
        all: true,
        event_type: selectedEventType !== 'All' ? selectedEventType : undefined,
        user_id: selectedUserId || undefined,
      };

      const res = await api.calendar.list(params);
      if (res.data?.data) {
        setEvents(res.data.data);
      }
    } catch (err) {
      console.error('Failed to fetch calendar events:', err);
    } finally {
      setLoading(false);
    }
  }, [selectedEventType, selectedUserId]);

  useEffect(() => {
    fetchMetadata();
  }, []);

  useEffect(() => {
    fetchEvents();
  }, [fetchEvents]);

  // Date Navigation Helpers
  const handlePrev = () => {
    setCurrentDate((prev) => {
      const d = new Date(prev);
      if (viewMode === 'Month') {
        d.setMonth(d.getMonth() - 1);
      } else if (viewMode === 'Week') {
        d.setDate(d.getDate() - 7);
      } else {
        d.setDate(d.getDate() - 1);
      }
      return d;
    });
  };

  const handleNext = () => {
    setCurrentDate((prev) => {
      const d = new Date(prev);
      if (viewMode === 'Month') {
        d.setMonth(d.getMonth() + 1);
      } else if (viewMode === 'Week') {
        d.setDate(d.getDate() + 7);
      } else {
        d.setDate(d.getDate() + 1);
      }
      return d;
    });
  };

  const handleToday = () => {
    setCurrentDate(new Date());
  };

  // Delete event
  const handleDeleteEvent = async (id) => {
    if (!window.confirm('Are you sure you want to delete this event?')) return;
    try {
      await api.calendar.delete(id);
      setSelectedEventDetails(null);
      fetchEvents();
      fetchMetadata();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete event.');
    }
  };

  // Quick Reschedule (+1 day)
  const handleQuickReschedule = async (event, daysToAdd) => {
    try {
      const start = new Date(event.start_time);
      const end = new Date(event.end_time);
      start.setDate(start.getDate() + daysToAdd);
      end.setDate(end.getDate() + daysToAdd);

      await api.calendar.reschedule(event.id, {
        start_time: start.toISOString(),
        end_time: end.toISOString(),
      });
      fetchEvents();
      fetchMetadata();
      if (selectedEventDetails?.id === event.id) {
        setSelectedEventDetails({
          ...selectedEventDetails,
          start_time: start.toISOString(),
          end_time: end.toISOString(),
        });
      }
    } catch (err) {
      alert('Failed to reschedule event.');
    }
  };

  const getEventTypeColor = (type) => {
    switch (type) {
      case 'meeting':
        return 'bg-blue-50 text-blue-700 border-blue-200';
      case 'call':
        return 'bg-emerald-50 text-emerald-700 border-emerald-200';
      case 'demo':
        return 'bg-purple-50 text-purple-700 border-purple-200';
      case 'task':
        return 'bg-amber-50 text-amber-700 border-amber-200';
      case 'webinar':
        return 'bg-indigo-50 text-indigo-700 border-indigo-200';
      default:
        return 'bg-slate-100 text-slate-700 border-slate-200';
    }
  };

  const getEventIcon = (type) => {
    switch (type) {
      case 'meeting':
        return '🤝';
      case 'call':
        return '📞';
      case 'demo':
        return '🖥️';
      case 'task':
        return '✓';
      case 'webinar':
        return '🎓';
      default:
        return '📌';
    }
  };

  // Month View Days Builder
  const getMonthDays = () => {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);

    const startingDayIndex = firstDay.getDay(); // 0 = Sun
    const totalDays = lastDay.getDate();

    const days = [];

    // Preceding Month Days
    const prevMonthLastDay = new Date(year, month, 0).getDate();
    for (let i = startingDayIndex - 1; i >= 0; i--) {
      days.push({
        date: new Date(year, month - 1, prevMonthLastDay - i),
        isCurrentMonth: false,
      });
    }

    // Current Month Days
    for (let i = 1; i <= totalDays; i++) {
      days.push({
        date: new Date(year, month, i),
        isCurrentMonth: true,
      });
    }

    // Following Month Days to fill grid
    const remaining = 42 - days.length;
    for (let i = 1; i <= remaining; i++) {
      days.push({
        date: new Date(year, month + 1, i),
        isCurrentMonth: false,
      });
    }

    return days;
  };

  // Week View Days Builder
  const getWeekDays = () => {
    const d = new Date(currentDate);
    const day = d.getDay();
    const diff = d.getDate() - day; // start on Sunday
    const week = [];
    for (let i = 0; i < 7; i++) {
      week.push(new Date(d.getFullYear(), d.getMonth(), diff + i));
    }
    return week;
  };

  const isToday = (date) => {
    const today = new Date();
    return (
      date.getDate() === today.getDate() &&
      date.getMonth() === today.getMonth() &&
      date.getFullYear() === today.getFullYear()
    );
  };

  const getEventsForDate = (date) => {
    const dStr = date.toISOString().substring(0, 10);
    return events.filter((e) => e.start_time && e.start_time.substring(0, 10) === dStr);
  };

  const formatTimeRange = (start, end) => {
    if (!start) return '';
    const s = new Date(start);
    const e = end ? new Date(end) : null;
    const format = (d) =>
      d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
    return e ? `${format(s)} - ${format(e)}` : format(s);
  };

  const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];

  return (
    <div className="p-6 md:p-8 space-y-6">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div className="flex items-center space-x-3">
            <h1 className="text-2xl font-black text-slate-900 tracking-tight flex items-center">
              <span className="mr-2.5 text-2xl">📅</span> Calendar & Schedule
            </h1>
            <span className="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
              {stats.total_events} Events
            </span>
          </div>
          <p className="text-slate-500 text-xs mt-1">
            Coordinate meetings, product demos, calls, and follow-ups with leads, contacts, and deals.
          </p>
        </div>
        <div className="flex items-center space-x-3">
          <button
            onClick={() => {
              setEditingEvent(null);
              setModalInitialDate(new Date());
              setIsModalOpen(true);
            }}
            className="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-purple-500/20 transition flex items-center space-x-2"
          >
            <span>+ Schedule Event</span>
          </button>
        </div>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="p-4 bg-white rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
          <div>
            <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Bookings</p>
            <p className="text-2xl font-black text-slate-900 mt-1">{stats.total_events || 0}</p>
            <p className="text-[11px] text-slate-400 mt-0.5">Recorded CRM activities</p>
          </div>
          <div className="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xl">
            📅
          </div>
        </div>

        <div className="p-4 bg-white rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
          <div>
            <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Today's Agenda</p>
            <p className="text-2xl font-black text-emerald-600 mt-1">{stats.today_events || 0}</p>
            <p className="text-[11px] text-slate-400 mt-0.5">Events scheduled today</p>
          </div>
          <div className="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xl">
            ⚡
          </div>
        </div>

        <div className="p-4 bg-white rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
          <div>
            <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Upcoming Events</p>
            <p className="text-2xl font-black text-blue-600 mt-1">{stats.upcoming_events || 0}</p>
            <p className="text-[11px] text-slate-400 mt-0.5">Future scheduled sessions</p>
          </div>
          <div className="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xl">
            🎯
          </div>
        </div>
      </div>

      {/* Calendar Controls & Filter Bar */}
      <div className="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        {/* Navigation */}
        <div className="flex items-center space-x-3 w-full md:w-auto justify-between md:justify-start">
          <div className="flex items-center space-x-1.5 bg-slate-100 p-1 rounded-xl">
            <button
              onClick={handlePrev}
              className="p-1.5 hover:bg-white rounded-lg text-slate-600 font-bold transition text-xs shadow-sm"
              title="Previous"
            >
              ◀
            </button>
            <button
              onClick={handleToday}
              className="px-3 py-1 hover:bg-white rounded-lg text-slate-700 font-bold text-xs transition shadow-sm"
            >
              Today
            </button>
            <button
              onClick={handleNext}
              className="p-1.5 hover:bg-white rounded-lg text-slate-600 font-bold transition text-xs shadow-sm"
              title="Next"
            >
              ▶
            </button>
          </div>

          <h2 className="text-lg font-black text-slate-900 tracking-tight">
            {monthNames[currentDate.getMonth()]} {currentDate.getFullYear()}
            {viewMode === 'Week' && (
              <span className="text-xs font-semibold text-slate-400 ml-2">
                (Week of {getWeekDays()[0].toLocaleDateString([], { month: 'short', day: 'numeric' })})
              </span>
            )}
            {viewMode === 'Day' && (
              <span className="text-xs font-semibold text-slate-400 ml-2">
                ({currentDate.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' })})
              </span>
            )}
          </h2>
        </div>

        {/* View Switcher & Filters */}
        <div className="flex items-center space-x-3 w-full md:w-auto flex-wrap gap-2">
          {/* Filter Type */}
          <select
            value={selectedEventType}
            onChange={(e) => setSelectedEventType(e.target.value)}
            className="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
          >
            <option value="All">All Event Types</option>
            <option value="meeting">🤝 Meetings</option>
            <option value="call">📞 Calls</option>
            <option value="demo">🖥️ Demos</option>
            <option value="task">✓ Tasks</option>
            <option value="webinar">🎓 Webinars</option>
          </select>

          {/* Filter User */}
          <select
            value={selectedUserId}
            onChange={(e) => setSelectedUserId(e.target.value)}
            className="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
          >
            <option value="">All Team Hosts</option>
            {usersList.map((u) => (
              <option key={u.id} value={u.id}>
                {u.name}
              </option>
            ))}
          </select>

          {/* View Mode Buttons (Month, Week, Day) */}
          <div className="flex items-center bg-slate-100 p-1 rounded-xl">
            {['Month', 'Week', 'Day'].map((mode) => (
              <button
                key={mode}
                onClick={() => setViewMode(mode)}
                className={`px-3 py-1 rounded-lg text-xs font-bold transition ${
                  viewMode === mode
                    ? 'bg-purple-600 text-white shadow-md shadow-purple-500/20'
                    : 'text-slate-600 hover:text-slate-900'
                }`}
              >
                {mode}
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Main Calendar View Area */}
      <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        {loading ? (
          <div className="py-24 text-center">
            <div className="inline-block w-8 h-8 border-4 border-purple-600 border-t-transparent rounded-full animate-spin"></div>
            <p className="mt-3 text-xs font-semibold text-slate-500">Loading events from database...</p>
          </div>
        ) : (
          <>
            {/* MONTH VIEW */}
            {viewMode === 'Month' && (
              <div>
                {/* Day Headers */}
                <div className="grid grid-cols-7 border-b border-slate-100 bg-slate-50 text-center py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                  {['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map((d) => (
                    <div key={d}>{d}</div>
                  ))}
                </div>

                {/* Days Grid */}
                <div className="grid grid-cols-7 divide-x divide-y divide-slate-100">
                  {getMonthDays().map(({ date, isCurrentMonth }, idx) => {
                    const dayEvents = getEventsForDate(date);
                    const today = isToday(date);
                    return (
                      <div
                        key={idx}
                        onClick={() => {
                          setModalInitialDate(date);
                          setEditingEvent(null);
                          setIsModalOpen(true);
                        }}
                        className={`min-h-[110px] p-2 hover:bg-slate-50/80 transition cursor-pointer flex flex-col justify-between ${
                          !isCurrentMonth ? 'bg-slate-50/40 text-slate-300' : 'bg-white text-slate-800'
                        }`}
                      >
                        <div className="flex items-center justify-between mb-1.5">
                          <span
                            className={`w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold ${
                              today
                                ? 'bg-purple-600 text-white shadow-sm shadow-purple-500/30'
                                : isCurrentMonth
                                ? 'text-slate-700'
                                : 'text-slate-300'
                            }`}
                          >
                            {date.getDate()}
                          </span>
                          {dayEvents.length > 0 && (
                            <span className="text-[10px] font-bold text-purple-600">
                              {dayEvents.length} {dayEvents.length === 1 ? 'event' : 'events'}
                            </span>
                          )}
                        </div>

                        {/* Event Pills */}
                        <div className="space-y-1 overflow-y-auto max-h-[80px]">
                          {dayEvents.map((ev) => (
                            <div
                              key={ev.id}
                              onClick={(e) => {
                                e.stopPropagation();
                                setSelectedEventDetails(ev);
                              }}
                              className={`px-2 py-1 rounded-lg text-[11px] font-bold border truncate flex items-center space-x-1 hover:brightness-95 transition ${getEventTypeColor(
                                ev.event_type
                              )}`}
                            >
                              <span>{getEventIcon(ev.event_type)}</span>
                              <span className="truncate">{ev.title}</span>
                            </div>
                          ))}
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>
            )}

            {/* WEEK VIEW */}
            {viewMode === 'Week' && (
              <div>
                {/* Day Headers */}
                <div className="grid grid-cols-7 border-b border-slate-100 bg-slate-50 text-center py-3">
                  {getWeekDays().map((d, idx) => {
                    const today = isToday(d);
                    return (
                      <div key={idx} className="flex flex-col items-center">
                        <span className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                          {d.toLocaleDateString([], { weekday: 'short' })}
                        </span>
                        <span
                          className={`mt-1 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold ${
                            today
                              ? 'bg-purple-600 text-white shadow-sm shadow-purple-500/30'
                              : 'text-slate-800'
                          }`}
                        >
                          {d.getDate()}
                        </span>
                      </div>
                    );
                  })}
                </div>

                {/* Week Columns */}
                <div className="grid grid-cols-7 divide-x divide-slate-100 min-h-[480px]">
                  {getWeekDays().map((d, idx) => {
                    const dayEvents = getEventsForDate(d);
                    return (
                      <div
                        key={idx}
                        onClick={() => {
                          setModalInitialDate(d);
                          setEditingEvent(null);
                          setIsModalOpen(true);
                        }}
                        className="p-3 space-y-2 hover:bg-slate-50/50 transition cursor-pointer"
                      >
                        {dayEvents.length === 0 ? (
                          <div className="text-center py-10 text-slate-300 text-xs italic">
                            + Click to schedule
                          </div>
                        ) : (
                          dayEvents.map((ev) => (
                            <div
                              key={ev.id}
                              onClick={(e) => {
                                e.stopPropagation();
                                setSelectedEventDetails(ev);
                              }}
                              className={`p-2.5 rounded-xl border space-y-1 shadow-sm transition hover:scale-[1.02] cursor-pointer ${getEventTypeColor(
                                ev.event_type
                              )}`}
                            >
                              <div className="flex items-center justify-between">
                                <span className="text-xs">{getEventIcon(ev.event_type)}</span>
                                <span className="text-[10px] font-semibold opacity-75">
                                  {formatTimeRange(ev.start_time, ev.end_time)}
                                </span>
                              </div>
                              <p className="text-xs font-bold line-clamp-2">{ev.title}</p>
                              {ev.location && (
                                <p className="text-[10px] opacity-75 truncate">📍 {ev.location}</p>
                              )}
                              {ev.eventable && (
                                <p className="text-[10px] font-semibold opacity-80 truncate">
                                  🔗 {ev.eventable.type}: {ev.eventable.name}
                                </p>
                              )}
                            </div>
                          ))
                        )}
                      </div>
                    );
                  })}
                </div>
              </div>
            )}

            {/* DAY VIEW */}
            {viewMode === 'Day' && (
              <div className="p-6 space-y-4">
                <div className="flex items-center justify-between border-b border-slate-100 pb-3">
                  <h3 className="text-sm font-bold text-slate-800">
                    Schedule for {currentDate.toLocaleDateString([], { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })}
                  </h3>
                  <button
                    onClick={() => {
                      setModalInitialDate(currentDate);
                      setEditingEvent(null);
                      setIsModalOpen(true);
                    }}
                    className="px-3 py-1.5 bg-purple-600 text-white rounded-lg text-xs font-bold hover:bg-purple-700 transition"
                  >
                    + Add Event Today
                  </button>
                </div>

                {getEventsForDate(currentDate).length === 0 ? (
                  <div className="py-20 text-center space-y-2">
                    <div className="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xl mx-auto">
                      📅
                    </div>
                    <p className="text-sm font-bold text-slate-800">No events scheduled for this day</p>
                    <p className="text-xs text-slate-500">Plan a client demonstration, discovery call, or internal sync.</p>
                  </div>
                ) : (
                  <div className="space-y-3">
                    {getEventsForDate(currentDate).map((ev) => (
                      <div
                        key={ev.id}
                        onClick={() => setSelectedEventDetails(ev)}
                        className={`p-4 rounded-2xl border flex items-start justify-between transition hover:shadow-md cursor-pointer ${getEventTypeColor(
                          ev.event_type
                        )}`}
                      >
                        <div className="space-y-1.5">
                          <div className="flex items-center space-x-2">
                            <span className="text-lg">{getEventIcon(ev.event_type)}</span>
                            <h4 className="text-sm font-bold text-slate-900">{ev.title}</h4>
                            <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/80 border border-current capitalize">
                              {ev.status}
                            </span>
                          </div>
                          <p className="text-xs font-semibold text-slate-700">
                            ⏰ {formatTimeRange(ev.start_time, ev.end_time)}
                          </p>
                          {ev.location && (
                            <p className="text-xs text-slate-600">📍 {ev.location}</p>
                          )}
                          {ev.description && (
                            <p className="text-xs text-slate-600 mt-1">{ev.description}</p>
                          )}
                          {ev.eventable && (
                            <div className="inline-flex items-center space-x-1 px-2 py-0.5 rounded bg-white/70 text-[11px] font-bold text-slate-800 mt-1">
                              <span>🔗 Linked {ev.eventable.type}:</span>
                              <span>{ev.eventable.name}</span>
                            </div>
                          )}
                        </div>

                        <div className="flex items-center space-x-1.5">
                          <button
                            onClick={(e) => {
                              e.stopPropagation();
                              handleQuickReschedule(ev, 1);
                            }}
                            className="px-2.5 py-1 rounded-lg bg-white/90 text-[11px] font-bold text-slate-700 hover:bg-white shadow-sm transition"
                            title="Reschedule +1 day"
                          >
                            +1 Day ⏩
                          </button>
                          <button
                            onClick={(e) => {
                              e.stopPropagation();
                              setEditingEvent(ev);
                              setIsModalOpen(true);
                            }}
                            className="p-1.5 bg-white/90 text-slate-700 rounded-lg text-xs font-bold hover:bg-white shadow-sm transition"
                            title="Edit Event"
                          >
                            ✏️
                          </button>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            )}
          </>
        )}
      </div>

      {/* Event Details Drawer / Modal Popover */}
      {selectedEventDetails && (
        <div className="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 border border-slate-100 animate-in zoom-in-95">
            <div className="flex justify-between items-start">
              <div className="flex items-center space-x-3">
                <span className="text-2xl">{getEventIcon(selectedEventDetails.event_type)}</span>
                <div>
                  <h3 className="text-base font-bold text-slate-900">{selectedEventDetails.title}</h3>
                  <p className="text-xs text-slate-500 capitalize">{selectedEventDetails.event_type} • {selectedEventDetails.status}</p>
                </div>
              </div>
              <button
                onClick={() => setSelectedEventDetails(null)}
                className="text-slate-400 hover:text-slate-600 text-sm font-bold"
              >
                ✕
              </button>
            </div>

            <div className="space-y-2 text-xs text-slate-700 bg-slate-50 p-3.5 rounded-xl border border-slate-100">
              <p>
                <strong className="text-slate-900">⏰ Timing:</strong>{' '}
                {formatTimeRange(selectedEventDetails.start_time, selectedEventDetails.end_time)} (
                {new Date(selectedEventDetails.start_time).toLocaleDateString()})
              </p>
              {selectedEventDetails.location && (
                <p>
                  <strong className="text-slate-900">📍 Location:</strong> {selectedEventDetails.location}
                </p>
              )}
              {selectedEventDetails.user && (
                <p>
                  <strong className="text-slate-900">👤 Organizer:</strong> {selectedEventDetails.user.name} ({selectedEventDetails.user.email})
                </p>
              )}
              {selectedEventDetails.eventable && (
                <p>
                  <strong className="text-slate-900">🔗 Linked Entity:</strong>{' '}
                  <span className="font-bold text-purple-700">
                    {selectedEventDetails.eventable.type}: {selectedEventDetails.eventable.name}
                  </span>
                </p>
              )}
              {selectedEventDetails.description && (
                <p className="pt-2 border-t border-slate-200">
                  <strong className="text-slate-900">Notes / Agenda:</strong>
                  <br />
                  {selectedEventDetails.description}
                </p>
              )}
            </div>

            {/* Actions */}
            <div className="flex items-center justify-between pt-2">
              <button
                onClick={() => handleDeleteEvent(selectedEventDetails.id)}
                className="px-3 py-1.5 bg-red-50 text-red-700 rounded-xl text-xs font-bold hover:bg-red-100 transition"
              >
                🗑 Delete
              </button>
              <div className="flex items-center space-x-2">
                <button
                  onClick={() => {
                    handleQuickReschedule(selectedEventDetails, 1);
                  }}
                  className="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-200 transition"
                >
                  ⏩ +1 Day
                </button>
                <button
                  onClick={() => {
                    setEditingEvent(selectedEventDetails);
                    setSelectedEventDetails(null);
                    setIsModalOpen(true);
                  }}
                  className="px-4 py-1.5 bg-purple-600 text-white rounded-xl text-xs font-bold hover:bg-purple-700 transition"
                >
                  ✏️ Edit
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Add / Edit Event Modal */}
      <CalendarEventModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        onSave={() => {
          fetchEvents();
          fetchMetadata();
        }}
        event={editingEvent}
        initialDate={modalInitialDate}
      />
    </div>
  );
}
