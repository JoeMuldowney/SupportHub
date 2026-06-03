from dash import Dash, html, dcc, Input, Output, State, dash_table
import plotly.express as px
import dash_bootstrap_components as dbc
from sqlalchemy import create_engine
import pandas as pd
import numpy as np

def read_secret(path: str) -> str:
    with open(path, "r") as f:
        return f.read().strip()

db_host = read_secret("/run/secrets/db_host")
db_name = read_secret("/run/secrets/db_name")
db_user = read_secret("/run/secrets/db_user")
db_pass = read_secret("/run/secrets/db_pass")
db_port = read_secret("/run/secrets/db_port")

# Database connection
engine = create_engine(
    f"mysql+pymysql://{db_user}:{db_pass}@{db_host}:{db_port}/{db_name}"
)

# Initialize app
app = Dash(__name__, 
           external_stylesheets=[dbc.themes.COSMO],
           meta_tags=[{"name": "viewport", "content": "width=device-width, initial-scale=1"}])

def fetch_data():
    """Fetches and merges task and user data from SQL."""
    try:
        query = """
            SELECT 
                t.id, t.priority, t.status, t.category, t.date_opened, t.date_closed, 
                t.user_desc, t.manager as task_manager,
                u.first_name, u.last_name, u.dept, u.role, u.manager as user_manager
            FROM task t
            LEFT JOIN users u ON t.user_id = u.user_id
        """
        df = pd.read_sql(query, engine)
        df['date_opened'] = pd.to_datetime(df['date_opened'])
        df['date_closed'] = pd.to_datetime(df['date_closed'])
        
        # Consolidate manager name
        df['manager_name'] = df['user_manager'].fillna(df['task_manager']).fillna('Unassigned').str.strip()
        df.loc[df['manager_name'] == '', 'manager_name'] = 'Unassigned'
        
        # Calculate resolution time in days
        df['resolution_days'] = (df['date_closed'] - df['date_opened']).dt.days
        return df
    except Exception as e:
        print(f"Error fetching data: {e}")
        return pd.DataFrame(columns=['id', 'priority', 'status', 'category', 'date_opened', 
                                   'date_closed', 'user_desc', 'first_name', 'last_name', 'dept', 
                                   'resolution_days', 'role', 'user_manager', 'manager_name'])

# UI Helper: Create a KPI Card
def create_card(title, value, color, icon_class):
    return dbc.Card(
        dbc.CardBody([
            html.Div([
                html.I(className=icon_class + " fa-2x mb-2"),
                html.H4(title, className="card-title"),
                html.H2(value, className="card-text font-weight-bold", id=f"kpi-{title.lower().replace(' ', '-')}")
            ], className="text-center")
        ]),
        color=color,
        inverse=True,
        className="mb-4 shadow-sm transition-hover"
    )

# DASH LAYOUT
app.layout = dbc.Container([
    # Header & Mode Toggle
    dbc.Row([
        dbc.Col([
            html.Div([
                html.H1("SupportHub Service Dashboard", className="display-4 font-weight-bold"),
                html.P("Real-time Ticket Analysis & Performance Metrics", className="lead text-muted"),
                dbc.Row([
                    dbc.Col([
                        dbc.Label("Dashboard Mode:", className="mr-2 font-weight-bold"),
                        dbc.RadioItems(
                            id="view-mode-toggle",
                            options=[
                                {"label": "Global Overview", "value": "overview"},
                                {"label": "Team Breakdown", "value": "team"},
                                {"label": "Individual User", "value": "user"},
                            ],
                            value="overview",
                            inline=True,
                            className="d-inline-block ml-3",
                        ),
                    ], width={"size": 8, "offset": 2})
                ]),
                html.Hr(className="my-3"),
            ], className="text-center py-4")
        ], width=12)
    ]),
    
    # KPI Row
    dbc.Row([
        dbc.Col(create_card("Total Tickets", "0", "primary", "fas fa-ticket-alt"), lg=3, md=6),
        dbc.Col(create_card("Open Tickets", "0", "warning", "fas fa-folder-open"), lg=3, md=6),
        dbc.Col(create_card("Urgent Priority", "0", "danger", "fas fa-exclamation-triangle"), lg=3, md=6),
        dbc.Col(create_card("Avg Resolution", "0d", "success", "fas fa-clock"), lg=3, md=6),
    ]),

    # Filters & Search Section
    dbc.Card([
        dbc.CardHeader(html.H5("Filters & Search", className="mb-0")),
        dbc.CardBody([
            dbc.Row([
                dbc.Col([
                    dbc.Label("Search Descriptions / Users"),
                    dbc.Input(id="search-input", placeholder="Type keywords...", type="text", className="mb-3"),
                ], lg=3, md=6),
                dbc.Col([
                    dbc.Label("Category"),
                    dcc.Dropdown(id="category-filter", multi=True, placeholder="All Categories", className="mb-3"),
                ], lg=2, md=6),
                dbc.Col([
                    dbc.Label("Status"),
                    dcc.Dropdown(id="status-filter", multi=True, placeholder="All Statuses", className="mb-3"),
                ], lg=2, md=6),
                dbc.Col([
                    dbc.Label("Priority"),
                    dcc.Dropdown(id="priority-filter", multi=True, placeholder="All Priorities", className="mb-3"),
                ], lg=2, md=6),
                dbc.Col([
                    dbc.Label("Manager / Team"),
                    dcc.Dropdown(id="manager-filter", multi=True, placeholder="Select Manager", className="mb-3"),
                ], lg=3, md=6, id="manager-filter-col"),
            ])
        ])
    ], className="mb-4 shadow-sm border-0"),

    # Main Visuals Row
    dbc.Row([
        dbc.Col([
            dbc.Card([
                dbc.CardBody([dcc.Graph(id="main-chart-1", config={'displayModeBar': False})])
            ], className="shadow-sm border-0 h-100")
        ], lg=5, md=12, className="mb-4"),
        
        dbc.Col([
            dbc.Card([
                dbc.CardBody([dcc.Graph(id="main-chart-2", config={'displayModeBar': False})])
            ], className="shadow-sm border-0 h-100")
        ], lg=7, md=12, className="mb-4"),
    ]),

    # Trend Line
    dbc.Row([
        dbc.Col([
            dbc.Card([
                dbc.CardBody([dcc.Graph(id="trend-line")])
            ], className="shadow-sm border-0 mb-4")
        ], width=12)
    ]),

    # Detailed Data Table
    dbc.Row([
        dbc.Col([
            dbc.Card([
                dbc.CardHeader(html.H5("Detailed Record History", className="mb-0")),
                dbc.CardBody([
                    dash_table.DataTable(
                        id='ticket-table',
                        columns=[
                            {"name": "ID", "id": "id"},
                            {"name": "Priority", "id": "priority"},
                            {"name": "Status", "id": "status"},
                            {"name": "Category", "id": "category"},
                            {"name": "Opened By", "id": "full_name"},
                            {"name": "Manager", "id": "manager_name"},
                            {"name": "Department", "id": "dept"},
                            {"name": "Date Opened", "id": "date_opened_str"},
                        ],
                        page_size=10,
                        sort_action="native",
                        filter_action="native",
                        style_table={'overflowX': 'auto'},
                        style_header={
                            'backgroundColor': '#f8f9fa',
                            'fontWeight': 'bold',
                            'border': '1px solid #dee2e6'
                        },
                        style_cell={
                            'textAlign': 'left',
                            'padding': '12px',
                            'fontFamily': 'inherit',
                            'border': '1px solid #dee2e6'
                        },
                        style_data_conditional=[
                            {
                                'if': {'column_id': 'priority', 'filter_query': '{priority} eq "High"'},
                                'color': '#dc3545', 'fontWeight': 'bold'
                            },
                            {
                                'if': {'column_id': 'status', 'filter_query': '{status} eq "Closed"'},
                                'color': '#28a745'
                            }
                        ]
                    )
                ])
            ], className="shadow-sm border-0 mb-5")
        ], width=12)
    ])

], fluid=True, style={"backgroundColor": "#f4f7f6", "minHeight": "100vh"})

# CALLBACKS
@app.callback(
    [Output("main-chart-1", "figure"),
     Output("main-chart-2", "figure"),
     Output("trend-line", "figure"),
     Output("ticket-table", "data"),
     Output("category-filter", "options"),
     Output("status-filter", "options"),
     Output("priority-filter", "options"),
     Output("manager-filter", "options"),
     Output("kpi-total-tickets", "children"),
     Output("kpi-open-tickets", "children"),
     Output("kpi-urgent-priority", "children"),
     Output("kpi-avg-resolution", "children")],
    [Input("search-input", "value"),
     Input("category-filter", "value"),
     Input("status-filter", "value"),
     Input("priority-filter", "value"),
     Input("manager-filter", "value"),
     Input("view-mode-toggle", "value")]
)
def update_dashboard(search, categories, statuses, priorities, managers, view_mode):
    df = fetch_data()
    if df.empty:
        return [px.scatter(title="No Data Source Found")] * 3 + [[]] + [[]] * 4 + ["0"] * 4

    df['full_name'] = df['first_name'].fillna('') + " " + df['last_name'].fillna('')
    df['date_opened_str'] = df['date_opened'].dt.strftime('%Y-%m-%d')
    
    # Dropdown Options
    manager_options = [{"label": x, "value": x} for x in sorted(df['manager_name'].unique())]
    cat_options = [{"label": x, "value": x} for x in sorted(df['category'].dropna().unique())]
    stat_options = [{"label": x, "value": x} for x in sorted(df['status'].dropna().unique())]
    prio_options = [{"label": x, "value": x} for x in sorted(df['priority'].dropna().unique())]

    # Apply Filters
    dff = df.copy()
    if search:
        search_lower = search.lower()
        dff = dff[
            dff['user_desc'].str.lower().str.contains(search_lower, na=False) | 
            dff['full_name'].str.lower().str.contains(search_lower, na=False) |
            dff['id'].astype(str).str.contains(search_lower, na=False)
        ]
    if categories:
        dff = dff[dff['category'].isin(categories)]
    if statuses:
        dff = dff[dff['status'].isin(statuses)]
    if priorities:
        dff = dff[dff['priority'].isin(priorities)]
    if managers:
        dff = dff[dff['manager_name'].isin(managers)]

    if dff.empty:
        empty_fig = px.scatter(title="No Data Matches Selected Filters")
        empty_fig.update_layout(xaxis={'visible': False}, yaxis={'visible': False})
        return [empty_fig] * 3 + [[]] + [cat_options, stat_options, prio_options, manager_options] + ["0"] * 4

    # Chart Logic
    if view_mode == 'overview':
        fig1 = px.pie(dff, names="status", hole=0.5, title="<b>Global Status Distribution</b>")
        counts = dff.groupby("category").size().reset_index(name="count")
        fig2 = px.bar(counts, x="category", y="count", color="category", title="<b>Tickets by Category</b>")
    elif view_mode == 'team':
        fig1 = px.pie(dff, names="status", hole=0.5, title="<b>Team Status Distribution</b>")
        team_counts = dff.groupby("manager_name").size().reset_index(name="count")
        fig2 = px.bar(team_counts, x="manager_name", y="count", color="manager_name", title="<b>Ticket Volume by Team</b>")
        fig2.update_layout(xaxis_title="Manager / Team")
    else:
        fig1 = px.pie(dff, names="category", hole=0.5, title="<b>Individual Category Focus</b>")
        user_status = dff.groupby(["full_name", "status"]).size().reset_index(name="count")
        fig2 = px.bar(user_status, x="full_name", y="count", color="status", title="<b>Individual Status Breakdown</b>", barmode="group")
        fig2.update_layout(xaxis_title="User Name")

    fig1.update_layout(margin=dict(t=40, b=20, l=20, r=20))
    fig2.update_layout(showlegend=False, margin=dict(t=40, b=20, l=20, r=20))

    trend_df = dff.groupby(dff['date_opened'].dt.date).size().reset_index(name="count")
    fig_trend = px.area(trend_df, x="date_opened", y="count", title="<b>Ticket Volume Trend</b>")
    fig_trend.update_traces(line_color='#007bff', fillcolor='rgba(0, 123, 255, 0.1)')

    total_val = len(dff)
    open_val = len(dff[dff['status'].str.lower().isin(['open', 'pending', 'in progress'])])
    urgent_val = len(dff[dff['priority'].str.lower() == 'high'])
    avg_res = dff['resolution_days'].mean()
    avg_res_str = f"{avg_res:.1f}d" if not np.isnan(avg_res) else "N/A"

    return (fig1, fig2, fig_trend, dff.to_dict('records'), 
            cat_options, stat_options, prio_options, manager_options,
            str(total_val), str(open_val), str(urgent_val), avg_res_str)

if __name__ == "__main__":
    app.run(debug=True, host='0.0.0.0', port=8000)
