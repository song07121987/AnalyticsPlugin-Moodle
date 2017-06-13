USE [MoodleDBOld]
GO
ALTER TABLE [dbo].[mdl_analytic_user_language] DROP CONSTRAINT [DF__mdl_analy__use_1__31832429]
GO
ALTER TABLE [dbo].[mdl_analytic_user_dashboard] DROP CONSTRAINT [DF__mdl_analyt__name__2EA6B77E]
GO
ALTER TABLE [dbo].[mdl_analytic_segment] DROP CONSTRAINT [DF__mdl_analy__delet__1A9FBED1]
GO
ALTER TABLE [dbo].[mdl_analytic_segment] DROP CONSTRAINT [DF__mdl_analy__auto___19AB9A98]
GO
ALTER TABLE [dbo].[mdl_analytic_segment] DROP CONSTRAINT [DF__mdl_analy__enabl__18B7765F]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__custo__68143F04]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__custo__67201ACB]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__custo__662BF692]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__custo__6537D259]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__custo__6443AE20]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__custo__634F89E7]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__custo__625B65AE]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__custo__61674175]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__custo__60731D3C]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__custo__5F7EF903]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__reven__5E8AD4CA]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__reven__5D96B091]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__reven__5CA28C58]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__reven__5BAE681F]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__reven__5ABA43E6]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__items__59C61FAD]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__idord__58D1FB74]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__locat__57DDD73B]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__locat__56E9B302]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__locat__55F58EC9]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__locat__55016A90]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__refer__540D4657]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__refer__5319221E]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__refer__5224FDE5]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__refer__5130D9AC]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__idlin__503CB573]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] DROP CONSTRAINT [DF__mdl_analy__idact__4F48913A]
GO
ALTER TABLE [dbo].[mdl_analytic_goal] DROP CONSTRAINT [DF__mdl_analy__delet__4A83DC1D]
GO
/****** Object:  Table [dbo].[mdl_analytic_user_language]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_user_language]
GO
/****** Object:  Table [dbo].[mdl_analytic_user_dashboard]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_user_dashboard]
GO
/****** Object:  Table [dbo].[mdl_analytic_user]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_user]
GO
/****** Object:  Table [dbo].[mdl_analytic_site_url]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_site_url]
GO
/****** Object:  Table [dbo].[mdl_analytic_site_setting]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_site_setting]
GO
/****** Object:  Table [dbo].[mdl_analytic_site]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_site]
GO
/****** Object:  Table [dbo].[mdl_analytic_session]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_session]
GO
/****** Object:  Table [dbo].[mdl_analytic_sequence]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_sequence]
GO
/****** Object:  Table [dbo].[mdl_analytic_segment]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_segment]
GO
/****** Object:  Table [dbo].[mdl_analytic_report]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_report]
GO
/****** Object:  Table [dbo].[mdl_analytic_periods]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_periods]
GO
/****** Object:  Table [dbo].[mdl_analytic_option]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_option]
GO
/****** Object:  Table [dbo].[mdl_analytic_logger_message]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_logger_message]
GO
/****** Object:  Table [dbo].[mdl_analytic_log_visit]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_log_visit]
GO
/****** Object:  Table [dbo].[mdl_analytic_log_profiling]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_log_profiling]
GO
/****** Object:  Table [dbo].[mdl_analytic_log_link_visit_action]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_log_link_visit_action]
GO
/****** Object:  Table [dbo].[mdl_analytic_log_conversion_item]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_log_conversion_item]
GO
/****** Object:  Table [dbo].[mdl_analytic_log_conversion]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_log_conversion]
GO
/****** Object:  Table [dbo].[mdl_analytic_log_action]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_log_action]
GO
/****** Object:  Table [dbo].[mdl_analytic_goal]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_goal]
GO
/****** Object:  Table [dbo].[mdl_analytic_errorlog]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_errorlog]
GO
/****** Object:  Table [dbo].[mdl_analytic_access]    Script Date: 7/4/2017 11:38:06 AM ******/
DROP TABLE [dbo].[mdl_analytic_access]
GO
/****** Object:  Table [dbo].[mdl_analytic_access]    Script Date: 7/4/2017 11:38:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_access](
	[login] [nvarchar](100) NOT NULL,
	[idsite] [bigint] NOT NULL,
	[access] [nvarchar](10) NULL,
PRIMARY KEY CLUSTERED 
(
	[login] ASC,
	[idsite] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[mdl_analytic_errorlog]    Script Date: 7/4/2017 11:38:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_errorlog](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[appname] [nvarchar](50) NOT NULL DEFAULT (''),
	[version] [nvarchar](20) NOT NULL DEFAULT (''),
	[os] [nvarchar](50) NOT NULL DEFAULT (''),
	[language] [nvarchar](20) NOT NULL DEFAULT (''),
	[manufacturer] [nvarchar](50) NOT NULL DEFAULT (''),
	[device] [nvarchar](100) NOT NULL DEFAULT (''),
	[resolution] [nvarchar](15) NOT NULL DEFAULT (''),
	[orientation] [nvarchar](12) NOT NULL DEFAULT (''),
	[online] [bigint] NOT NULL,
	[diskspace] [bigint] NOT NULL,
	[deviceid] [nvarchar](50) NOT NULL DEFAULT (''),
	[userid] [bigint] NOT NULL,
	[errordate] [datetime] NOT NULL,
	[description] [nvarchar](max) NULL,
	[stack] [nvarchar](max) NULL,
	[nonfatal] [bigint] NOT NULL,
	[runtime] [bigint] NOT NULL,
	[insertdate] [datetime] NOT NULL,
	[ip] [nvarchar](50) NOT NULL DEFAULT (''),
	[url] [nvarchar](max) NULL,
	[errorlevel] [nvarchar](20) NOT NULL DEFAULT (''),
	[environment] [nvarchar](max) NULL,
	[useragent] [nvarchar](max) NULL,
 CONSTRAINT [mdl_analerro_id_pk] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[mdl_analytic_goal]    Script Date: 7/4/2017 11:38:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_goal](
	[idsite] [int] NOT NULL,
	[idgoal] [int] NOT NULL,
	[name] [nvarchar](50) NOT NULL,
	[match_attribute] [nvarchar](20) NOT NULL,
	[pattern] [nvarchar](255) NOT NULL,
	[pattern_type] [nvarchar](10) NOT NULL,
	[case_sensitive] [int] NOT NULL,
	[allow_multiple] [int] NOT NULL,
	[revenue] [float] NOT NULL,
	[deleted] [int] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[idsite] ASC,
	[idgoal] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[mdl_analytic_log_action]    Script Date: 7/4/2017 11:38:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_log_action](
	[idaction] [bigint] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](max) NULL,
	[hash] [bigint] NOT NULL,
	[type] [int] NULL,
	[url_prefix] [tinyint] NULL,
 CONSTRAINT [PK__mdl_analytic_lo__5026ACF9CE21F198] PRIMARY KEY CLUSTERED 
(
	[idaction] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[mdl_analytic_log_conversion]    Script Date: 7/4/2017 11:38:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[mdl_analytic_log_conversion](
	[idvisit] [bigint] NOT NULL,
	[idsite] [bigint] NOT NULL,
	[idvisitor] [binary](8) NOT NULL,
	[server_time] [datetime] NOT NULL,
	[idaction_url] [int] NULL,
	[idlink_va] [int] NULL,
	[referer_visit_server_date] [date] NULL,
	[referer_type] [bigint] NULL,
	[referer_name] [nvarchar](70) NULL,
	[referer_keyword] [nvarchar](255) NULL,
	[visitor_returning] [smallint] NOT NULL,
	[visitor_count_visits] [int] NOT NULL,
	[visitor_days_since_first] [int] NOT NULL,
	[visitor_days_since_order] [int] NOT NULL,
	[location_country] [nvarchar](3) NOT NULL,
	[location_region] [nvarchar](2) NULL,
	[location_city] [nvarchar](255) NULL,
	[location_latitude] [float] NULL,
	[location_longitude] [float] NULL,
	[url] [text] NOT NULL,
	[idgoal] [int] NOT NULL,
	[buster] [bigint] NOT NULL,
	[idorder] [nvarchar](100) NULL,
	[items] [int] NULL,
	[revenue] [float] NULL,
	[revenue_subtotal] [float] NULL,
	[revenue_tax] [float] NULL,
	[revenue_shipping] [float] NULL,
	[revenue_discount] [float] NULL,
	[custom_var_k1] [nvarchar](200) NULL,
	[custom_var_v1] [nvarchar](200) NULL,
	[custom_var_k2] [nvarchar](200) NULL,
	[custom_var_v2] [nvarchar](200) NULL,
	[custom_var_k3] [nvarchar](200) NULL,
	[custom_var_v3] [nvarchar](200) NULL,
	[custom_var_k4] [nvarchar](200) NULL,
	[custom_var_v4] [nvarchar](200) NULL,
	[custom_var_k5] [nvarchar](200) NULL,
	[custom_var_v5] [nvarchar](200) NULL,
PRIMARY KEY CLUSTERED 
(
	[idvisit] ASC,
	[idgoal] ASC,
	[buster] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[mdl_analytic_log_conversion_item]    Script Date: 7/4/2017 11:38:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[mdl_analytic_log_conversion_item](
	[idsite] [bigint] NOT NULL,
	[idvisitor] [binary](8) NOT NULL,
	[server_time] [datetime] NOT NULL,
	[idvisit] [bigint] NOT NULL,
	[idorder] [nvarchar](100) NOT NULL,
	[idaction_sku] [bigint] NOT NULL,
	[idaction_name] [bigint] NOT NULL,
	[idaction_category] [bigint] NOT NULL,
	[idaction_category2] [bigint] NOT NULL,
	[idaction_category3] [bigint] NOT NULL,
	[idaction_category4] [bigint] NOT NULL,
	[idaction_category5] [bigint] NOT NULL,
	[price] [float] NOT NULL,
	[quantity] [bigint] NOT NULL,
	[deleted] [tinyint] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[idvisit] ASC,
	[idorder] ASC,
	[idaction_sku] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[mdl_analytic_log_link_visit_action]    Script Date: 7/4/2017 11:38:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_log_link_visit_action](
	[idlink_va] [bigint] IDENTITY(1,1) NOT NULL,
	[idsite] [bigint] NOT NULL,
	[idvisitor] [nvarchar](200) NOT NULL,
	[server_time] [datetime] NOT NULL,
	[idvisit] [bigint] NOT NULL,
	[idaction_url] [bigint] NULL CONSTRAINT [DF__mdl_analytic_log__idact__44A01A3E]  DEFAULT (NULL),
	[idaction_url_ref] [bigint] NULL CONSTRAINT [DF__mdl_analytic_log__idact__45943E77]  DEFAULT ((0)),
	[idaction_name] [bigint] NULL,
	[idaction_name_ref] [bigint] NOT NULL,
	[idaction_event_category] [bigint] NULL CONSTRAINT [DF__mdl_analytic_log__idact__468862B0]  DEFAULT (NULL),
	[idaction_event_action] [bigint] NULL CONSTRAINT [DF__mdl_analytic_log__idact__477C86E9]  DEFAULT (NULL),
	[idaction_content_interaction] [bigint] NULL CONSTRAINT [DF__mdl_analytic_log__idact__4870AB22]  DEFAULT (NULL),
	[idaction_content_name] [bigint] NULL CONSTRAINT [DF__mdl_analytic_log__idact__4964CF5B]  DEFAULT (NULL),
	[idaction_content_piece] [bigint] NULL CONSTRAINT [DF__mdl_analytic_log__idact__4A58F394]  DEFAULT (NULL),
	[idaction_content_target] [bigint] NULL CONSTRAINT [DF__mdl_analytic_log__idact__4B4D17CD]  DEFAULT (NULL),
	[time_spent_ref_action] [bigint] NOT NULL,
	[custom_var_k1] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__4C413C06]  DEFAULT (NULL),
	[custom_var_v1] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__4D35603F]  DEFAULT (NULL),
	[custom_var_k2] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__4E298478]  DEFAULT (NULL),
	[custom_var_v2] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__4F1DA8B1]  DEFAULT (NULL),
	[custom_var_k3] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__5011CCEA]  DEFAULT (NULL),
	[custom_var_v3] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__5105F123]  DEFAULT (NULL),
	[custom_var_k4] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__51FA155C]  DEFAULT (NULL),
	[custom_var_v4] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__52EE3995]  DEFAULT (NULL),
	[custom_var_k5] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__53E25DCE]  DEFAULT (NULL),
	[custom_var_v5] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__54D68207]  DEFAULT (NULL),
	[custom_float] [float] NULL CONSTRAINT [DF__mdl_analytic_log__custo__55CAA640]  DEFAULT (NULL),
 CONSTRAINT [PK__mdl_analytic_lo__E21C3C4F782F19AA] PRIMARY KEY CLUSTERED 
(
	[idlink_va] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[mdl_analytic_log_profiling]    Script Date: 7/4/2017 11:38:07 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_log_profiling](
	[query] [nvarchar](max) NOT NULL,
	[count] [bigint] NULL,
	[sum_time_ms] [float] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[mdl_analytic_log_visit]    Script Date: 7/4/2017 11:38:07 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_log_visit](
	[idvisit] [bigint] IDENTITY(1,1) NOT NULL,
	[idsite] [bigint] NOT NULL,
	[idvisitor] [nvarchar](max) NOT NULL,
	[user_id] [nvarchar](200) NULL,
	[visitor_localtime] [time](7) NOT NULL,
	[visitor_returning] [smallint] NOT NULL,
	[visitor_count_visits] [int] NOT NULL,
	[visitor_days_since_last] [int] NOT NULL,
	[visitor_days_since_order] [int] NOT NULL,
	[visitor_days_since_first] [int] NOT NULL,
	[visit_first_action_time] [datetime] NOT NULL,
	[visit_last_action_time] [datetime] NOT NULL,
	[visit_exit_idaction_url] [bigint] NULL CONSTRAINT [DF__mdl_analytic_log__visit__16D94F8E]  DEFAULT ((0)),
	[visit_exit_idaction_name] [bigint] NOT NULL,
	[visit_entry_idaction_url] [bigint] NOT NULL,
	[visit_entry_idaction_name] [bigint] NOT NULL,
	[visit_total_actions] [int] NOT NULL,
	[visit_total_searches] [int] NOT NULL,
	[visit_total_events] [int] NOT NULL,
	[visit_total_time] [int] NOT NULL,
	[visit_goal_converted] [smallint] NOT NULL,
	[visit_goal_buyer] [smallint] NOT NULL,
	[referer_type] [smallint] NULL,
	[referer_name] [nvarchar](70) NULL,
	[referer_url] [nvarchar](max) NOT NULL,
	[referer_keyword] [nvarchar](255) NULL,
	[config_id] [nvarchar](200) NOT NULL,
	[config_os] [nchar](3) NOT NULL,
	[config_os_version] [nvarchar](100) NULL,
	[config_browser_engine] [nvarchar](10) NOT NULL,
	[config_browser_name] [nvarchar](10) NOT NULL,
	[config_browser_version] [nvarchar](20) NOT NULL,
	[config_resolution] [nvarchar](9) NOT NULL,
	[config_device_brand] [nvarchar](100) NULL,
	[config_device_model] [nvarchar](100) NULL,
	[config_device_type] [tinyint] NULL,
	[config_pdf] [smallint] NOT NULL,
	[config_flash] [smallint] NOT NULL,
	[config_java] [smallint] NOT NULL,
	[config_director] [smallint] NOT NULL,
	[config_quicktime] [smallint] NOT NULL,
	[config_realplayer] [smallint] NOT NULL,
	[config_windowsmedia] [smallint] NOT NULL,
	[config_gears] [smallint] NOT NULL,
	[config_silverlight] [smallint] NOT NULL,
	[config_cookie] [smallint] NOT NULL,
	[location_ip] [nvarchar](200) NOT NULL,
	[location_browser_lang] [nvarchar](20) NOT NULL,
	[location_country] [nvarchar](3) NOT NULL,
	[location_region] [nvarchar](2) NULL CONSTRAINT [DF__mdl_analytic_log__locat__17CD73C7]  DEFAULT (NULL),
	[location_city] [nvarchar](255) NULL CONSTRAINT [DF__mdl_analytic_log__locat__18C19800]  DEFAULT (NULL),
	[location_latitude] [float] NULL CONSTRAINT [DF__mdl_analytic_log__locat__19B5BC39]  DEFAULT (NULL),
	[location_longitude] [float] NULL CONSTRAINT [DF__mdl_analytic_log__locat__1AA9E072]  DEFAULT (NULL),
	[custom_var_k1] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__1B9E04AB]  DEFAULT (NULL),
	[custom_var_v1] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__1C9228E4]  DEFAULT (NULL),
	[custom_var_k2] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__1D864D1D]  DEFAULT (NULL),
	[custom_var_v2] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__1E7A7156]  DEFAULT (NULL),
	[custom_var_k3] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__1F6E958F]  DEFAULT (NULL),
	[custom_var_v3] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__2062B9C8]  DEFAULT (NULL),
	[custom_var_k4] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__2156DE01]  DEFAULT (NULL),
	[custom_var_v4] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__224B023A]  DEFAULT (NULL),
	[custom_var_k5] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__233F2673]  DEFAULT (NULL),
	[custom_var_v5] [nvarchar](200) NULL CONSTRAINT [DF__mdl_analytic_log__custo__24334AAC]  DEFAULT (NULL),
 CONSTRAINT [PK__mdl_analytic_lo__9C24F910AACD9152] PRIMARY KEY CLUSTERED 
(
	[idvisit] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[mdl_analytic_logger_message]    Script Date: 7/4/2017 11:38:07 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_logger_message](
	[idlogger_message] [bigint] IDENTITY(1,1) NOT NULL,
	[tag] [nvarchar](50) NULL,
	[timestamp] [datetime] NULL,
	[level] [nvarchar](16) NULL,
	[message] [text] NULL,
PRIMARY KEY CLUSTERED 
(
	[idlogger_message] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[mdl_analytic_option]    Script Date: 7/4/2017 11:38:07 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_option](
	[option_name] [nvarchar](255) NOT NULL,
	[option_value] [text] NOT NULL,
	[autoload] [tinyint] NOT NULL DEFAULT ('1'),
PRIMARY KEY CLUSTERED 
(
	[option_name] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[mdl_analytic_periods]    Script Date: 7/4/2017 11:38:07 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_periods](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](30) NULL,
	[ordering] [int] NULL,
	[start] [int] NULL,
	[end] [int] NULL,
	[display] [nvarchar](50) NULL,
 CONSTRAINT [PK_mdl_analytic_periods] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[mdl_analytic_report]    Script Date: 7/4/2017 11:38:07 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_report](
	[idreport] [int] IDENTITY(1,1) NOT NULL,
	[idsite] [int] NOT NULL,
	[login] [nvarchar](100) NOT NULL,
	[description] [nvarchar](255) NOT NULL,
	[idsegment] [int] NULL,
	[period] [nvarchar](10) NOT NULL,
	[hour] [tinyint] NOT NULL,
	[type] [nvarchar](10) NOT NULL,
	[format] [nvarchar](10) NOT NULL,
	[reports] [text] NOT NULL,
	[parameters] [text] NULL,
	[ts_created] [datetime] NULL,
	[ts_last_sent] [datetime] NULL,
	[deleted] [tinyint] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[idreport] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[mdl_analytic_segment]    Script Date: 7/4/2017 11:38:07 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[mdl_analytic_segment](
	[idsegment] [int] IDENTITY(1,1) NOT NULL,
	[name] [varchar](255) NOT NULL,
	[definition] [text] NOT NULL,
	[login] [varchar](100) NOT NULL,
	[enable_all_users] [tinyint] NOT NULL,
	[enable_only_idsite] [int] NULL,
	[auto_archive] [tinyint] NOT NULL,
	[ts_created] [datetime] NULL,
	[ts_last_edit] [datetime] NULL,
	[deleted] [tinyint] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[idsegment] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[mdl_analytic_sequence]    Script Date: 7/4/2017 11:38:07 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_sequence](
	[name] [nvarchar](120) NOT NULL,
	[value] [bigint] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[name] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[mdl_analytic_session]    Script Date: 7/4/2017 11:38:07 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_session](
	[id] [nvarchar](255) NOT NULL,
	[modified] [int] NULL,
	[lifetime] [int] NULL,
	[data] [text] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[mdl_analytic_site]    Script Date: 7/4/2017 11:38:07 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_site](
	[idsite] [bigint] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](90) NOT NULL,
	[main_url] [nvarchar](255) NOT NULL,
	[ts_created] [datetime] NULL,
	[ecommerce] [tinyint] NULL DEFAULT ((0)),
	[sitesearch] [tinyint] NULL DEFAULT ((1)),
	[sitesearch_keyword_parameters] [text] NOT NULL,
	[sitesearch_category_parameters] [text] NOT NULL,
	[timezone] [nvarchar](50) NOT NULL,
	[currency] [nchar](3) NOT NULL,
	[exclude_unknown_urls] [tinyint] NULL DEFAULT ((0)),
	[excluded_ips] [text] NOT NULL,
	[excluded_parameters] [text] NOT NULL,
	[excluded_user_agents] [text] NOT NULL,
	[group] [nvarchar](250) NOT NULL,
	[type] [nvarchar](255) NOT NULL,
	[keep_url_fragment] [tinyint] NOT NULL DEFAULT ((0)),
PRIMARY KEY CLUSTERED 
(
	[idsite] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[mdl_analytic_site_setting]    Script Date: 7/4/2017 11:38:07 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_site_setting](
	[idsite] [bigint] IDENTITY(1,1) NOT NULL,
	[setting_name] [nvarchar](255) NOT NULL,
	[setting_value] [text] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[idsite] ASC,
	[setting_name] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[mdl_analytic_site_url]    Script Date: 7/4/2017 11:38:07 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_site_url](
	[idsite] [bigint] NOT NULL,
	[url] [nvarchar](255) NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[idsite] ASC,
	[url] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[mdl_analytic_user]    Script Date: 7/4/2017 11:38:07 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_user](
	[login] [nvarchar](100) NOT NULL,
	[password] [nchar](32) NOT NULL,
	[alias] [nvarchar](45) NOT NULL,
	[email] [nvarchar](100) NOT NULL,
	[token_auth] [nchar](32) NOT NULL,
	[superuser_access] [tinyint] NOT NULL DEFAULT ('0'),
	[date_registered] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[login] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
UNIQUE NONCLUSTERED 
(
	[token_auth] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[mdl_analytic_user_dashboard]    Script Date: 7/4/2017 11:38:07 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[mdl_analytic_user_dashboard](
	[login] [varchar](100) NOT NULL,
	[iddashboard] [int] NOT NULL,
	[name] [varchar](100) NULL,
	[layout] [text] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[login] ASC,
	[iddashboard] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[mdl_analytic_user_language]    Script Date: 7/4/2017 11:38:07 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mdl_analytic_user_language](
	[login] [nvarchar](100) NOT NULL,
	[language] [nvarchar](10) NOT NULL,
	[use_12_hour_clock] [tinyint] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[login] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
ALTER TABLE [dbo].[mdl_analytic_goal] ADD  DEFAULT ('0') FOR [deleted]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [idaction_url]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [idlink_va]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [referer_visit_server_date]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [referer_type]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [referer_name]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [referer_keyword]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [location_region]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [location_city]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [location_latitude]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [location_longitude]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [idorder]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [items]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [revenue]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [revenue_subtotal]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [revenue_tax]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [revenue_shipping]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [revenue_discount]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [custom_var_k1]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [custom_var_v1]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [custom_var_k2]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [custom_var_v2]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [custom_var_k3]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [custom_var_v3]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [custom_var_k4]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [custom_var_v4]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [custom_var_k5]
GO
ALTER TABLE [dbo].[mdl_analytic_log_conversion] ADD  DEFAULT (NULL) FOR [custom_var_v5]
GO
ALTER TABLE [dbo].[mdl_analytic_segment] ADD  DEFAULT ((0)) FOR [enable_all_users]
GO
ALTER TABLE [dbo].[mdl_analytic_segment] ADD  DEFAULT ((0)) FOR [auto_archive]
GO
ALTER TABLE [dbo].[mdl_analytic_segment] ADD  DEFAULT ((0)) FOR [deleted]
GO
ALTER TABLE [dbo].[mdl_analytic_user_dashboard] ADD  DEFAULT (NULL) FOR [name]
GO
ALTER TABLE [dbo].[mdl_analytic_user_language] ADD  DEFAULT ('0') FOR [use_12_hour_clock]
GO
