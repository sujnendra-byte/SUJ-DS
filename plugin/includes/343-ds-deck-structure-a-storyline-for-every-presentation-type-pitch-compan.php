<?php
/**
 * Code Snippets export — DS Deck Structure - a storyline for every presentation type (pitch, company overview, sales, report ...)
 *
 * Snippet ID : 1178
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ds-deck-structure
 * Modified   : 2026-10-15 15:41:24 (V41 update from staging)
 * Load order : 343 of 345
 */
/* DS Deck Structure - gives every kind of presentation its own storyline.
 * Reads the user's request, works out the kind of deck (pitch, company overview, sales, report ...),
 * and replaces the fixed slide plan in the AI prompt with that kind's standard sections.
 * Does not edit the Template Engine. To switch off: deactivate this snippet.
 * Data format:  @key|Label|keywords   then   Kind|Priority|Section|Guidance
 *   Kind: C content, S stats, L timeline, Q quote.  Priority 1 = always kept, 4 = only in long decks. */
if ( ! defined( 'ABSPATH' ) ) return;

function ds_dp_data() {
	return <<<'DSDP'
@pitch|Investor pitch deck|pitch deck,investor pitch,investor deck,investor presentation,fundrais,seed round,series a ,series b ,pre-seed,venture capital,angel invest,raise capital,raising capital,funding round,startup pitch,pitch for investors,demo day,pitch to investors
C|1|The problem|The painful, costly problem the target customer has today, with proof of how big and frequent it is
C|1|Our solution|What the product is and how it removes the problem, in plain words
C|3|Why now|The market, technology or regulatory shift that makes this the right moment
S|1|Market size|Total, serviceable and obtainable market figures with clear assumptions
C|2|Product|How it works: key features, the user experience and what is live today
C|1|Business model|Who pays, pricing, unit economics and the main revenue streams
S|1|Traction|Revenue, users, growth rate, pilots or other proof that it is working
C|2|Go-to-market|How customers are won: channels, partnerships and the sales motion
C|2|Competition|The main alternatives and our advantage against each
C|2|Team|Founders and key people, and the experience that makes them right for this
L|2|Roadmap|Milestones for the next 12 to 24 months
S|3|Financial projections|Three-year revenue, margin and burn outlook
C|1|The ask|Amount being raised, use of funds and the milestones it pays for
C|4|Vision|The long-term picture if this succeeds
@company|Company overview|company overview,company profile,company presentation,corporate profile,corporate presentation,corporate overview,about us,about our company,who we are,company introduction,introduce our company,business profile,organisation overview,organization overview,firm profile,capability statement,company credentials,introduction of our company
C|1|Who we are|What the company does, for whom, and what makes it different
C|2|Mission, vision and values|The purpose and principles that guide decisions
L|2|Our story|Founding and the key milestones up to today
C|1|Products and services|The main offerings and what each delivers for the customer
C|3|Markets and industries served|Sectors, customer types and regions
S|1|Company in numbers|Years in business, people, customers, locations, revenue or output
C|2|Why clients choose us|Differentiators and strengths backed by specifics
C|2|Clients and results|Named or typed clients and the results achieved for them
C|3|How we work|Process, quality standards and certifications
C|3|Leadership team|Key leaders and their experience
C|4|Presence and facilities|Offices, plants or delivery centres
C|4|Awards and recognition|Awards, rankings and memberships that prove standing
C|4|Sustainability and community|Commitments and concrete actions
L|3|Where we are going|Growth plans and priorities for the coming years
@sales|Sales presentation|sales deck,sales presentation,sales pitch,client pitch,customer pitch,pitch to client,pitch to customer,pitch to a client,prospect,sell our,selling our,product pitch,solution pitch,offer to client,win the client,b2b pitch,sales proposal
C|1|Your challenge|The buyer's situation and the specific pains and costs they face
C|3|Cost of doing nothing|What the problem costs each month it is left unsolved
C|1|Our solution|What we offer and how it solves those pains
C|1|Key benefits|Outcomes the buyer gets, each tied to one of their pains
C|2|How it works|The steps or features that deliver the benefits
S|1|Proof in numbers|Results, return on investment, savings or performance figures from customers
C|2|Customer success story|One customer: their situation, what we did and the result
Q|4|Customer voice|A short testimonial from a satisfied customer
C|3|Why us|What we do better than the alternatives
C|2|Packages and pricing|Options, what is included and the commercial terms
L|2|Implementation plan|Onboarding steps and time to first value
C|3|Support and service|How the customer is looked after once they buy
C|4|Common questions|The usual objections with a clear answer to each
C|1|Next steps|The specific action and date we propose
@launch|Product launch plan|product launch,launch plan,launching,go-to-market,go to market,gtm plan,gtm strategy,new product,release plan,introducing our new,unveil
C|1|What we are launching|The product, who it is for and the promise it makes
C|2|The opportunity|The market need and why this is the moment
C|1|Target customers|Segments and buyer profiles, with what each cares about
C|1|Key features and benefits|What it does and why that matters to the customer
C|2|Positioning and messaging|How we describe it and the one line we want remembered
C|3|Pricing and packaging|Price points, bundles and offers at launch
C|1|Launch channels and activities|Marketing, sales and partner activities that carry the launch
L|1|Launch timeline|Pre-launch, launch day and the weeks after
S|2|Targets and KPIs|The numbers that define a successful launch
C|3|Sales and support readiness|Training, materials and service capacity
C|3|Competitive landscape|Who else is there and how we win
C|4|Budget and resources|Spend by activity and the team behind it
C|4|Risks and mitigation|What could go wrong and the plan for each
@marketing|Marketing plan|marketing plan,marketing strategy,marketing presentation,marketing campaign,campaign plan,campaign proposal,brand strategy,branding,social media,content strategy,digital marketing,seo ,advertis,media plan,influencer,lead generation,demand generation,email marketing,content marketing
C|1|Situation and goals|Where the brand stands and what marketing must achieve
C|1|Target audience|Who we want to reach, their needs and where they spend time
S|2|Market and performance insights|Current reach, conversion, share or research figures
C|2|Positioning and key messages|What we stand for and the messages to repeat
C|1|Strategy and big idea|The central idea and how it will win attention
C|1|Channels and tactics|What we will do on each channel
C|2|Content and creative plan|Formats, themes and examples of content
L|1|Campaign calendar|Phases and key dates
S|1|Budget and KPIs|Spend by channel and the targets it must hit
C|3|Competitor review|What competitors are doing and the gap we can own
C|3|Customer journey|Touchpoints from first contact to repeat purchase
C|4|Team and tools|Who does what and with which tools
C|3|Measurement and reporting|How results are tracked and reviewed
@bizplan|Business plan|business plan,business model,startup plan,new business,venture plan,expansion plan,franchise plan,restaurant plan,shop plan,business idea
C|1|Executive summary|The business, the opportunity and what is being asked for
C|1|Business concept and offer|What we sell and why customers will buy it
S|1|Market opportunity|Market size, growth and the share we target
C|2|Target customers|Who they are and what they need
C|2|Competition and advantage|Competitors and why we will win
C|1|Marketing and sales plan|How customers are reached and converted
C|2|Operations plan|Location, suppliers, process and capacity
C|3|Team and organisation|Key people and structure
S|1|Financial plan|Revenue, costs, profit and break-even
C|3|Funding needs|How much is needed and what it pays for
L|2|Milestones|Key dates from setup to scale
C|3|Risks and mitigation|Main risks and the response to each
C|4|Legal and licences|Structure, permits and compliance
C|4|Growth vision|Where the business goes after year three
@proposal|Proposal|proposal,rfp ,tender,quotation,scope of work,statement of work,sow ,engagement plan,offer document,project bid,grant proposal,request for proposal,bid for
C|1|Understanding your needs|The client's situation and goals in their own terms
C|2|Objectives and success criteria|What success looks like and how it is measured
C|1|Our proposed approach|How we will solve it and why this way
C|1|Scope and deliverables|What is included, and what is not
L|1|Timeline and milestones|Phases with dates or durations
C|2|Team and roles|Who will do the work and their experience
S|2|Investment and pricing|Fees by item or phase and the total
C|2|Why us|The reasons to choose us, backed by specifics
C|3|Relevant experience|Similar work and the results achieved
C|3|Assumptions and dependencies|What we rely on from the client
C|3|Risks and how we manage them|Main delivery risks and safeguards
C|4|Governance and reporting|Meetings, reports and escalation
C|4|Terms and conditions|Payment, validity and key terms
C|1|Next steps|What happens after approval
@report|Business report or review|annual report,annual review,quarterly report,quarterly review,quarterly business review,qbr,monthly report,weekly report,business review,performance report,performance review,results presentation,year in review,progress report,sales report,kpi report,management report,operations report,mid-year review,half-year,year-end review,year end review,quarterly update
C|1|Executive summary|The period in brief: what happened and what it means
S|1|Key numbers|The headline figures for the period against target or last period
C|1|Highlights and achievements|The biggest wins with specifics
C|2|Performance by area|Results by division, product, region or team
C|2|Customers and market|Customer results, satisfaction and market movements
C|1|Challenges and lessons|What fell short, why, and what we learned
S|3|Financial summary|Revenue, cost and profit figures
C|3|People and operations|Team, capacity and operational changes
C|3|Projects and initiatives|Status of the main initiatives
C|4|Risks and outlook|What may affect the next period
C|1|Priorities for the next period|The few things that matter most next
L|2|Plan and milestones ahead|Key dates for the next period
@status|Project status update|project status,status update,status report,project update,progress update,sprint review,weekly update,steering committee,project review,milestone review
C|1|Summary and overall status|Where the project stands on scope, time and budget
L|1|Timeline and milestones|What is done, what is next and the dates
C|1|Completed since last update|Work finished in this period
C|2|In progress|Work under way and who owns it
S|2|Key metrics|Percent complete, budget used, schedule variance and quality figures
C|1|Risks and issues|Open risks and issues with owner and action
C|2|Decisions needed|What must be decided, by whom and by when
C|3|Budget and resources|Spend against plan and resource gaps
C|3|Dependencies|What the project waits on from others
C|4|Quality and testing|Test results and open defects
C|4|Stakeholder feedback|What sponsors and users are saying
C|1|Next steps|Actions before the next update
@projectplan|Project plan or kickoff|project plan,project kickoff,kick-off,kickoff,project charter,implementation plan,rollout plan,action plan,work plan,execution plan,migration plan,deployment plan
C|1|Background and goals|Why the project exists and what it must achieve
C|1|Scope|What is in and what is out
C|2|Deliverables|The concrete outputs
C|1|Approach and workstreams|How the work is organised
L|1|Timeline and phases|Phases with dates and milestones
C|2|Team and roles|Who is responsible for what
S|3|Budget and resources|Budget by item and resources needed
C|2|Risks and mitigation|Main risks and the plan for each
C|3|Communication and governance|Meetings, reporting and decision rights
C|3|Success measures|How we will know it worked
C|4|Dependencies and assumptions|What the plan relies on
C|4|Tools and ways of working|How the team will collaborate
C|1|Next steps|First actions and owners
@strategy|Strategy or annual plan|strategy,strategic plan,strategic,annual plan,vision 20,okr,objectives and key results,growth plan,five-year plan,5-year plan,three-year plan,3-year plan,long-term plan,business strategy,corporate strategy,priorities for 20,goals for 20
C|1|Where we are today|The current position, honestly stated
C|2|Market and trends|What is changing around us
C|3|Strengths and gaps|What we can build on and what holds us back
C|1|Vision and goals|Where we want to be and by when
C|1|Strategic priorities|The few choices that will get us there
C|2|Key initiatives|The main programmes behind each priority
S|1|Targets and KPIs|The numbers we commit to
L|1|Roadmap|When each priority is delivered
C|2|Resources and investment|People, money and capabilities required
C|3|Organisation and capabilities|How the organisation must change
C|3|Risks and assumptions|What could derail the plan
C|4|Governance and review|How progress is reviewed
C|4|Quick wins|What we can deliver in the first 90 days
@financial|Financial presentation|financial results,financial report,financial review,earnings,budget presentation,budget proposal,budget plan,annual budget,forecast,investor update,shareholder,board meeting,profit and loss,balance sheet,cash flow,financial performance,fiscal year,financial plan
C|1|Financial highlights|The period in brief for a finance audience
S|1|Headline numbers|Revenue, profit, margin and cash figures
C|1|Revenue analysis|What drove revenue up or down
C|2|Cost and margin analysis|Where costs moved and the effect on margin
C|2|Profitability and cash flow|Profit, cash generated and working capital
C|3|Balance sheet and funding|Assets, debt and liquidity
C|3|Performance by segment|Results by business line or region
C|2|Variance against budget|Where we beat or missed plan and why
S|2|Forecast and outlook|Expected figures for the coming period
C|3|Investments and capital allocation|Where money is being put to work
C|3|Risks and sensitivities|What could change the numbers
L|4|Financial calendar|Reporting and decision dates ahead
C|1|Decisions and next steps|What is requested from the audience
@training|Training or workshop|training,workshop,how to ,tutorial,course,masterclass,bootcamp,learning session,upskilling,certification,best practices,guide to,step by step,step-by-step,tips for,skills for
C|1|Learning objectives|What participants will be able to do afterwards
C|2|Why this matters|The cost of getting it wrong and the benefit of doing it well
C|1|Core concepts|The ideas everyone must understand first
C|1|Step-by-step method|The method in clear ordered steps
C|2|Worked example|One example taken through the method
C|2|Tools and techniques|Practical tools that make it easier
C|3|Common mistakes to avoid|Typical errors and how to prevent them
C|3|Best practices|What experienced people do differently
C|3|Practice activity|An exercise for participants to try
S|4|Facts and figures|Numbers that show why the skill matters
L|4|Learning path|What to learn next and in what order
C|1|Key takeaways|The points to remember and apply tomorrow
C|4|Resources|Where to learn more
@lesson|Lesson or educational topic|lesson,class presentation,school,students,grade ,lecture,chapter,syllabus,explain,introduction to,basics of,fundamentals of,history of,science,biology,chemistry,physics,geography,mathematics,essay,seminar on,assignment,what is 
C|1|Introduction|What the topic is and why it matters
C|2|Background and context|Where it comes from and what came before
C|1|Key concepts|The main ideas, defined simply
C|1|How it works|The mechanism, process or argument explained
C|2|Examples|Clear examples that make it concrete
S|2|Facts and figures|Important numbers, dates and measurements
L|2|Timeline or stages|How it developed, or its stages in order
C|3|Types or categories|The main kinds and how they differ
C|3|Advantages and disadvantages|Benefits and drawbacks
C|3|Real-world applications|Where it is used in daily life or industry
C|4|Common misconceptions|What people often get wrong
C|4|Current developments|What is new or changing
C|1|Summary|The essentials in a few points
@research|Research presentation|research,thesis,dissertation,study ,findings,literature review,methodology,hypothesis,experiment,survey results,academic,scientific,phd,capstone,viva,research paper
C|1|Background and research question|The problem and the question being answered
C|2|Literature and gap|What is known and what was missing
C|2|Objectives and hypotheses|What the study set out to test
C|1|Methodology|Design, sample, instruments and analysis
C|3|Data and sample|Who or what was studied, and how much
S|1|Key results|The main figures found
C|1|Findings and analysis|What the results show
C|2|Discussion|What the findings mean against earlier work
C|3|Limitations|What limits the conclusions
C|2|Conclusions|The answers to the research question
C|3|Recommendations and future work|What should be done or studied next
L|4|Research timeline|Stages of the study
@casestudy|Case study|case study,success story,customer story,client story,case analysis,use case
C|1|Client and context|Who the client is and their situation
C|1|The challenge|The problem and what was at stake
C|2|Goals|What the client wanted to achieve
C|1|Our solution|What we proposed and why
L|2|How we delivered|The steps and the time each took
S|1|Results|The measurable outcomes
Q|3|Client quote|A short quote from the client about the result
C|2|Key success factors|What made it work
C|3|Lessons learned|What we would repeat and what we would change
C|3|What comes next|The next phase with this client
@market|Market or competitor analysis|market research,market analysis,competitor analysis,competitive analysis,industry analysis,industry overview,market study,market trends,market report,competitive landscape,benchmark,consumer insights,trend report,market overview
C|1|Scope and key questions|What was studied and what we needed to know
S|1|Market size and growth|Size, growth rate and forecast figures
C|1|Key trends|The shifts shaping the market
C|2|Customer segments and needs|Who buys and what they want
C|1|Competitive landscape|The main players and how they compete
C|2|Competitor profiles|Strengths and weaknesses of the key competitors
C|3|Pricing and positioning|How prices and positions compare
C|3|Regulation and external factors|Rules, economy and technology affecting the market
C|2|Opportunities and threats|Where to play and what to watch
L|3|Market outlook|How the market is expected to develop
C|1|Implications and recommendations|What we should do with these findings
C|4|Method and sources|How the analysis was done
@swot|SWOT analysis|swot
C|1|Context and purpose|What is being assessed and why
C|1|Strengths|Internal advantages, with evidence
C|1|Weaknesses|Internal gaps, honestly stated
C|1|Opportunities|External openings we can use
C|1|Threats|External risks we must manage
C|2|Key insights|What the four lists tell us together
C|2|Strategic options|Ways to use strengths and close gaps
L|3|Action plan|Actions in order with timing
S|3|Supporting numbers|Figures behind the assessment
C|4|Risks to watch|Signals to monitor
@talk|Keynote, talk or webinar|keynote,conference talk,ted talk,tedx,webinar,speech,thought leadership,guest lecture,panel discussion,motivational,inspirational,future of,trends in,state of the
C|1|Opening hook|A surprising fact, question or short story that earns attention
C|1|The big idea|The single idea the audience should leave with
C|2|Why it matters now|What has changed and what is at stake
C|1|First key point|The first supporting argument, with an example
C|1|Second key point|The second supporting argument, with an example
C|2|Third key point|The third supporting argument, with an example
S|2|The evidence|Numbers that make the case hard to ignore
C|3|A story from the field|A concrete story that shows the idea working
Q|3|A line to remember|A short, quotable statement of the idea
C|3|What stands in the way|The usual objections or obstacles and how to get past them
L|4|How we got here|The developments that led to this moment
C|1|What you can do|Practical actions the audience can take this week
@onboarding|Employee onboarding or orientation|onboarding,orientation,new hire,new employee,new joiner,induction,welcome to the team,welcome aboard,employee handbook,first day
C|1|Welcome|A warm welcome and what this session covers
C|1|About the company|What we do, who we serve and what we stand for
C|2|Culture and values|How we behave and what we expect of each other
C|2|Organisation and key people|Structure and who to go to for what
C|1|Your role and team|Responsibilities, team and how success is judged
L|1|Your first 90 days|What happens in week one, month one and month three
C|2|Tools and systems|The systems you will use and how to get access
C|2|Policies to know|Working hours, leave, conduct, security and safety
C|3|Benefits and perks|Pay, benefits and support available
C|3|Learning and growth|Training, feedback and career paths
S|4|Company in numbers|Figures that show our size and reach
C|3|Who to ask for help|Contacts for people, IT and facilities questions
@townhall|Town hall or internal update|town hall,townhall,all-hands,all hands,internal update,team meeting,staff meeting,company update,leadership update,employee update,team update,internal communication,department update
C|1|Where we stand|An honest summary of the business right now
S|1|Results in numbers|The key figures since the last update
C|1|Wins and recognition|Achievements and the people behind them
C|2|Customer and market news|What customers are saying and what is changing outside
C|1|Priorities ahead|The few things we must get right next
C|2|Changes and announcements|Organisation, policy or product changes and what they mean for people
C|3|People and culture|Hiring, engagement and wellbeing
C|3|Challenges we face|What is hard right now and how we are responding
L|2|Key dates|What is coming and when
C|4|Team spotlight|One team and what it delivered
C|2|Questions and how to engage|How to ask questions and give feedback
@hiring|Recruitment or employer brand|recruitment,hiring plan,we are hiring,job opening,employer brand,careers,talent acquisition,campus hiring,campus placement,job fair,why join,work with us,internship program
C|1|Who we are|The company and its mission in a few lines
C|1|Why work with us|What makes this a good place to build a career
C|2|Culture and values|What daily working life is like
C|1|Open roles|The roles, what each does and who it suits
C|2|Growth and learning|Training, mentoring and career progression
C|2|Benefits and rewards|Pay, benefits, flexibility and perks
S|3|Our people in numbers|Team size, growth, retention and diversity figures
Q|3|Employee voice|A short quote from a current employee
C|3|Life at the company|Teams, offices, events and community
L|1|Hiring process|The steps from application to offer and the timing
C|1|How to apply|What to send, where and by when
@event|Event plan|event plan,event proposal,conference plan,event management,annual day,summit,exhibition,trade show,festival,gala,award ceremony,awards night,hackathon,offsite,team outing,product event,wedding plan,seminar plan
C|1|Event overview|What the event is, for whom, when and where
C|1|Objectives and audience|What it must achieve and who will attend
C|2|Theme and experience|The concept and what attendees will feel
C|1|Programme and agenda|Sessions, speakers and activities
C|2|Venue and logistics|Location, layout, travel, catering and technology
C|2|Promotion and registration|How people hear about it and sign up
S|2|Budget|Cost by item and expected income
L|1|Planning timeline|Key dates from now to event day and after
C|3|Team and responsibilities|Who owns what
C|3|Sponsors and partners|Who is involved and what they get
C|3|Risks and contingency|What could go wrong and the backup plan
C|4|Success measures|Attendance, satisfaction and return targets
@sponsorship|Sponsorship proposal|sponsorship,sponsor deck,sponsor proposal,sponsorship proposal,sponsorship opportunity,become a sponsor,brand partnership,media kit
C|1|About us and the opportunity|Who we are and what is being offered to sponsors
S|1|Audience and reach|Audience size, profile and engagement figures
C|1|Why partner with us|What the sponsor gains
C|1|Sponsorship packages|Tiers, what each includes and the price
C|2|Brand visibility and activation|Where and how the sponsor appears
C|2|Past sponsors and results|Who has sponsored before and what they achieved
C|3|Audience profile|Who the audience is and what they care about
L|3|Key dates|Deadlines and activation schedule
C|3|Custom options|How a package can be tailored
C|1|How to get involved|Contact and the next step
@nonprofit|Nonprofit, CSR or impact|nonprofit,non-profit,ngo,charity,fundraising,donor,donation,csr ,corporate social responsibility,social impact,impact report,community project,foundation,volunteer,social enterprise,grant application
C|1|The need|The problem, who is affected and how badly
C|1|Our mission and approach|What we do about it and why it works
C|1|Programmes|The main programmes and who each serves
S|1|Impact in numbers|People reached, outcomes achieved and cost per outcome
C|2|A story of change|One person or community and what changed for them
C|2|How funds are used|Where each unit of money goes
C|1|What we need|The funding or support requested and what it will achieve
C|3|Partners and supporters|Who stands with us
L|2|Plans ahead|What we will deliver and when
C|3|Governance and transparency|How we are run and held accountable
C|3|Ways to help|Give, volunteer, partner or advocate
@portfolio|Portfolio or credentials|portfolio,credentials,our work,showcase,agency deck,agency credentials,capabilities deck,capabilities presentation,design portfolio,project showcase,work samples,creative portfolio,studio profile
C|1|Who we are|What we do and our point of view
C|1|Services and expertise|What we offer and where we are strongest
C|2|Our approach|How we work from brief to delivery
C|1|Selected work: first project|The brief, what we made and the result
C|1|Selected work: second project|The brief, what we made and the result
C|2|Selected work: third project|The brief, what we made and the result
S|2|Results in numbers|Figures that prove the work performed
C|3|Clients|Who we have worked with
Q|3|Client words|A short quote from a client
C|3|Team|The people who do the work
C|4|Awards and press|Recognition received
C|2|Working with us|How an engagement starts and what it costs
@demo|Product overview or demo|product demo,product overview,product presentation,product walkthrough,software demo,app demo,platform overview,feature overview,product tour,saas ,our app,our platform,our software,mobile app,product brochure,product catalogue,product catalog
C|1|What it is|The product in one clear description and who uses it
C|1|The problem it solves|What users struggle with without it
C|1|Key features|The main features and what each does for the user
C|1|How it works|The path a user takes from start to result
C|2|Use cases|Typical situations where it is used
C|2|Integrations and technology|What it connects to and what it runs on
C|3|Security and reliability|How data is protected and uptime assured
S|2|Results for users|Time saved, cost reduced or performance gained
C|3|Who uses it|Customer types or named customers
C|2|Plans and pricing|Editions, what is included and the price
L|3|What is coming|Planned features and dates
C|1|Getting started|How to try, buy or set up
@bizcase|Business case|business case,cost benefit,cost-benefit,investment case,justification,feasibility,capex,request for approval,approval request,return on investment,roi analysis,make the case for
C|1|The issue|The problem or opportunity and why it needs a decision now
C|2|Current situation|How things work today and what it costs
C|1|Options considered|The realistic options, including doing nothing
C|1|Recommended option|What we recommend and why
S|1|Costs and benefits|Investment, savings, payback and return figures
C|2|Non-financial benefits|Quality, risk, customer or people benefits
C|2|Risks and mitigation|What could go wrong and how it is controlled
L|2|Implementation plan|Phases and dates
C|3|Resources required|People, budget and capabilities needed
C|3|Assumptions|What the numbers rely on
C|3|Success measures|How the benefit will be tracked
C|1|Decision requested|The exact approval being asked for
@change|Change or transformation|change management,transformation,digital transformation,restructuring,reorganisation,reorganization,merger,acquisition,integration plan,new operating model,process improvement,modernisation,modernization,automation programme,automation program,culture change,turnaround
C|1|Why change|What is no longer working and the cost of staying as we are
C|1|The future state|What it will look like when the change is done
C|2|What changes and what stays|The concrete differences for people and processes
C|1|Who is affected|Teams and roles affected, and how
C|1|The plan|The workstreams that deliver the change
L|1|Timeline and phases|When each stage happens
C|2|Communication and training|How people are informed and prepared
C|2|Support for people|Help available during the change
S|3|Expected benefits|The measurable gains
C|3|Risks and resistance|Likely concerns and how they are addressed
C|3|Governance and roles|Who leads, who decides
C|4|Early wins|Quick results that build confidence
C|1|What we ask of you|The specific actions expected of the audience
@risk|Risk, compliance, safety or policy|risk assessment,risk management,compliance,audit,internal audit,policy,code of conduct,cyber security,cybersecurity,information security,data protection,data privacy,gdpr,health and safety,workplace safety,safety training,fire safety,incident,business continuity,disaster recovery,governance,regulation,regulatory,anti-bribery,posh ,awareness
C|1|Purpose and scope|What this covers and who it applies to
C|2|Why it matters|What can go wrong and what it costs
C|1|Key risks or requirements|The main risks or rules, each stated plainly
C|2|Risk assessment|Likelihood and impact of each main risk
C|1|Controls and safeguards|What we do to prevent and detect problems
C|1|Roles and responsibilities|Who must do what
C|2|Do and do not|Clear expected behaviour with examples
C|2|Reporting and incident response|What to do when something goes wrong
S|3|Facts and figures|Incidents, audit results or industry numbers
C|3|Monitoring and review|How compliance is checked
L|3|Action plan|Improvements with dates
C|4|Training and resources|Where to learn more and get help
C|1|Key points to remember|The few rules everyone must follow
@realestate|Real estate or property|real estate,property,apartment,villa,housing project,residential project,commercial space,office space,plot ,township,construction project,architecture project,interior design,builder,listing presentation
C|1|Project overview|What the property or project is and who it is for
C|1|Location and connectivity|Where it is and what is nearby
C|1|Design and layout|Plans, unit types and sizes
C|2|Amenities and features|Facilities and specifications
S|1|Key figures|Area, units, price, yield or appreciation figures
C|2|Pricing and payment plans|Prices by unit type and payment options
C|3|Investment case|Why this is a sound purchase or investment
C|2|Developer and track record|Who is behind it and what they have delivered
L|2|Construction timeline|Stages and handover dates
C|3|Approvals and legal|Permits, titles and financing
C|4|Sustainability|Energy, water and green features
C|1|How to book|Site visits, booking steps and contact
@medical|Medical or healthcare|medical,clinical,patient,disease,treatment,healthcare,hospital,pharma,drug ,diagnosis,nursing,public health,surgery,therapy,mental health,nutrition,case presentation
C|1|Overview|What the condition or topic is and why it matters
S|2|Epidemiology and burden|How common it is and who is affected
C|1|Causes and risk factors|What causes it and who is at risk
C|1|Signs, symptoms and presentation|How it presents
C|1|Diagnosis and assessment|How it is identified and assessed
C|1|Treatment and management|The options and when each is used
C|2|Outcomes and prognosis|What to expect with and without treatment
C|2|Prevention|What reduces the risk
C|3|Guidelines and evidence|What current guidance and studies say
L|3|Care pathway|The stages of care in order
C|3|Complications|What can go wrong and the warning signs
C|4|Recent advances|What is new
C|1|Key messages|The points to remember in practice
@travel|Travel, tourism or destination|travel,tourism,destination,itinerary,trip plan,tour package,holiday,vacation,places to visit,hotel presentation,resort,hospitality,city guide,country profile
C|1|Destination overview|Where it is and what makes it special
C|1|Top attractions|The places not to miss and why
C|2|Experiences and activities|What visitors can do
C|2|Culture and food|Local customs, dishes and traditions
L|1|Suggested itinerary|A day-by-day plan
C|2|Where to stay|Areas and types of accommodation
C|2|Getting there and around|Transport options and travel times
S|2|Costs and key facts|Budget, climate, distances and visitor figures
C|3|Best time to visit|Seasons and events
C|3|Travel tips|Practical advice and things to avoid
C|4|Responsible travel|How to visit with respect
C|1|Booking and next steps|How to plan or book
@compare|Comparison or evaluation|comparison, vs ,versus,compare,evaluation,vendor selection,vendor evaluation,options analysis,which is better,alternatives,pros and cons,build vs buy,build or buy,shortlist
C|1|What we are deciding|The decision and why it matters
C|1|Evaluation criteria|What we judged the options on and the weight of each
C|1|First option|What it is, strengths and weaknesses
C|1|Second option|What it is, strengths and weaknesses
C|3|Third option|What it is, strengths and weaknesses
C|1|Side-by-side comparison|How the options compare on each criterion
S|2|Cost and performance figures|The numbers for each option
C|2|Risks of each option|What could go wrong with each
C|1|Recommendation|Which option and why
L|3|Path to implementation|Steps and timing once decided
C|3|Open questions|What still needs confirming
@process|Process, SOP or operations|process,sop ,standard operating procedure,workflow,procedure,operating model,operations plan,supply chain,logistics,manufacturing,quality management,lean ,six sigma,kaizen,playbook,framework,methodology,how we work
C|1|Purpose and scope|What the process achieves and where it starts and ends
C|1|Process overview|The stages from start to finish
C|1|Step-by-step detail|What happens at each step and who does it
C|2|Roles and responsibilities|Who owns, who approves, who is informed
C|2|Inputs, outputs and tools|What goes in, what comes out and the systems used
C|2|Standards and controls|Quality checks and rules that must be met
S|2|Performance measures|Cycle time, cost, quality and volume figures
C|3|Common problems and fixes|Where it usually goes wrong and what to do
C|3|Exceptions and escalation|What to do when the normal path does not apply
L|3|Improvement roadmap|Planned improvements and dates
C|4|Documents and templates|Forms and references
C|1|Key rules to remember|The essentials in a few points
@esg|ESG or sustainability|esg,sustainability,sustainable,net zero,net-zero,carbon,climate,environment,green energy,renewable,circular economy,emissions,diversity and inclusion,dei 
C|1|Our commitment|Why this matters to us and what we have promised
C|2|Material issues|The topics that matter most for our business and stakeholders
C|1|Environment|Energy, emissions, water and waste: actions and progress
C|1|People and community|Workforce, diversity, safety and community actions
C|2|Governance and ethics|How we are governed and held to account
S|1|Progress in numbers|Key indicators against targets
C|2|Flagship initiatives|The projects with the largest effect
L|1|Roadmap and targets|Milestones and target years
C|3|Supply chain and partners|How we work with suppliers on this
C|3|Challenges|Where we are behind and why
C|4|Standards and reporting|Frameworks followed and how data is assured
C|3|What comes next|The next actions
@partner|Partnership, franchise or distribution|partnership proposal,partnership,partner program,partner programme,franchise,distributor,dealership,reseller,channel partner,joint venture,collaboration proposal,alliance,mou ,affiliate program,vendor onboarding,supplier presentation
C|1|The opportunity|What we propose to do together and why
C|1|About us|Who we are and what we bring
C|1|What you gain|Revenue, reach, capability or other benefits to the partner
C|1|How the partnership works|The model, roles and what each side does
C|2|Commercial terms|Investment, margins, fees or revenue share
S|2|Market and proof|Market size and results from existing partners
C|2|Support we provide|Training, marketing, systems and service
C|3|Partner requirements|What we look for in a partner
C|3|Success stories|Existing partners and their results
L|2|Onboarding timeline|Steps from agreement to launch
C|4|Governance|How the relationship is managed
C|1|Next steps|How to proceed
@tech|Technical or architecture|architecture,system design,technical design,technical overview,tech stack,infrastructure,cloud migration,api ,database,devops,software engineering,data pipeline,machine learning model,ai model,network design,technical proposal,engineering review,design review,it strategy
C|1|Context and goals|The problem being solved and the requirements
C|2|Current state|How it works today and where it falls short
C|1|Proposed architecture|The main components and how they fit together
C|1|How it works|Data and request flow through the system
C|2|Technology choices|What we chose and why
C|2|Security and compliance|How access and data are protected
S|2|Performance and scale|Capacity, latency, availability and cost figures
C|3|Trade-offs and alternatives|What else was considered and why it was not chosen
C|3|Testing and quality|How correctness is assured
L|1|Delivery plan|Phases and milestones
C|3|Risks and open questions|What is uncertain and how it will be resolved
C|4|Operations and monitoring|How it is run and observed
C|1|Next steps|Decisions and actions needed
@roadmap|Product roadmap|product roadmap,roadmap,release plan,feature plan,product strategy,product vision,product plan,backlog,product update
C|1|Product vision|Where the product is heading and for whom
C|2|Where we are today|Current state, usage and what customers tell us
C|1|Strategic themes|The few themes the roadmap is built on
L|1|Roadmap|What ships now, next and later
C|1|Near-term releases|What is coming first and the value of each
C|2|Later bets|Bigger items further out
S|2|Goals and metrics|The numbers the roadmap is meant to move
C|3|Customer feedback and research|What we heard and how it shaped the plan
C|3|Dependencies and risks|What could delay delivery
C|3|What we are not doing|Requests set aside and why
C|4|Resources|Team and capacity behind the plan
C|2|How to give input|How stakeholders can influence priorities
@account|Account or customer review|account review,account plan,customer review,client review,key account,customer success,service review,client report,client update,vendor review,supplier review,renewal
C|1|Relationship summary|Where the relationship stands and what was agreed last time
S|1|Results delivered|The figures achieved for the customer in the period
C|1|What went well|Successes and value delivered
C|2|Service performance|Service levels, tickets and response against targets
C|1|Issues and how we fixed them|What went wrong and the corrective actions
C|2|Usage and adoption|How the customer uses the product or service
C|2|Customer goals ahead|What the customer wants to achieve next
C|2|Recommendations and opportunities|Where we can add more value
L|2|Joint plan|Agreed actions with dates
C|3|Commercials and renewal|Contract status and options
C|3|Team and contacts|Who is involved on both sides
C|1|Next steps|Actions and owners
@retro|Retrospective or lessons learned|retrospective,lessons learned,post-mortem,postmortem,post mortem,after action,project closure,project close,closure report,root cause,rca ,incident review
C|1|What we set out to do|The goal, scope and plan at the start
L|2|What happened|The sequence of events
S|2|Outcome in numbers|Results against target on time, cost and quality
C|1|What went well|Practices worth repeating
C|1|What did not go well|Problems, stated without blame
C|1|Root causes|Why the problems happened
C|1|Lessons learned|What we now know
C|1|Actions and owners|What changes, who owns it and by when
C|3|Impact|Effect on customers, team and business
C|3|Recognition|People and teams who made a difference
C|3|Open items|What is still unresolved
@personal|Personal profile or self-introduction|about me,self introduction,self-introduction,introduce myself,my profile,resume,curriculum vitae,biography,personal brand,job interview,my journey,my career,profile of,life of,tribute,farewell,retirement
C|1|Introduction|Who the person is in a few lines
L|1|Journey|Education and career milestones in order
C|1|Skills and strengths|What the person does best, with evidence
C|1|Key achievements|Results and recognitions
C|2|Projects and experience|Notable work and the role played
S|3|Career in numbers|Years, projects, teams or results
C|2|Values and working style|What drives the person and how they work
C|3|Interests|Life outside work
Q|4|In their words|A short personal statement
C|2|Goals ahead|What comes next
@teamintro|Team or department introduction|team introduction,meet the team,our team,department overview,department presentation,team overview,team profile,function overview,who does what
C|1|Who we are|The team's purpose and what it is responsible for
C|1|What we do|Services and outputs the team provides
C|1|Team structure and people|Roles and who holds them
C|2|How to work with us|How to make requests and what to expect
S|2|Team in numbers|Volume, results and service figures
C|2|Achievements|Recent results worth knowing
C|2|Current priorities|What the team is focused on
L|3|Plans ahead|What is coming and when
C|3|Tools and processes|How the work is organised
C|4|Challenges and needs|Where the team needs support
@perfreview|Performance or appraisal review|appraisal,performance appraisal,self appraisal,self-appraisal,employee review,my performance,year-end self review,promotion case,promotion deck,probation review
C|1|Role and goals for the period|What was expected
S|1|Results against goals|The figures achieved against each goal
C|1|Key achievements|The most valuable contributions, with specifics
C|2|Projects delivered|Projects and the role played
C|2|Skills developed|What was learned and applied
C|2|Feedback received|What colleagues and customers said
C|2|Challenges and lessons|What was hard and what was learned
C|1|Goals for the next period|What will be achieved next
C|3|Development plan|Skills to build and how
C|3|Support needed|What would help
L|4|Career path|Next roles and timing
@idea|Idea or innovation proposal|idea pitch,innovation,new idea,concept note,concept presentation,hackathon pitch,prototype,proof of concept,poc ,pilot proposal,design thinking,brainstorm,invention
C|1|The insight|What we noticed that others have missed
C|1|The problem|Who has it and why current answers fall short
C|1|The idea|What we propose, in plain words
C|1|How it works|The concept or prototype explained
C|2|Who benefits|Users and the value each gets
C|2|What makes it different|Why this is new and hard to copy
S|2|Size of the prize|The potential benefit in numbers
C|2|Feasibility|What is needed to build it and what is already proven
L|2|Pilot plan|Steps to test it and the timing
C|3|Costs and resources|What the pilot needs
C|3|Risks and unknowns|What we still need to learn
C|1|What we need|The decision or support requested
DSDP;
}

function ds_dp_types() {
	static $T = null;
	if ( $T !== null ) return $T;
	$T = array(); $cur = '';
	foreach ( explode( "\n", ds_dp_data() ) as $ln ) {
		$ln = rtrim( $ln, "\r" );
		if ( $ln === '' ) continue;
		if ( $ln[0] === '@' ) {
			$p = explode( '|', substr( $ln, 1 ), 3 );
			if ( count( $p ) < 3 ) { $cur = ''; continue; }
			$cur = trim( $p[0] );
			$T[ $cur ] = array( 'label' => trim( $p[1] ), 'kw' => explode( ',', $p[2] ), 'sec' => array() );
			continue;
		}
		if ( $cur === '' ) continue;
		$p = explode( '|', $ln, 4 );
		if ( count( $p ) < 4 ) continue;
		$T[ $cur ]['sec'][] = array( 'k' => $p[0], 'p' => (int) $p[1], 'n' => trim( $p[2] ), 'g' => trim( $p[3] ) );
	}
	$extra = array(
		'pitch'       => 'pitch,startup,investors,funding',
		'company'     => 'our company,my company,company deck,corporate deck,introduction deck,credentials deck',
		'sales'       => 'sales,for a client,for clients,for customers',
		'report'      => 'report,review,results,performance,quarterly,annual,monthly',
		'strategy'    => 'vision,goals,objectives,priorities',
		'financial'   => 'budget,finance,financial,revenue',
		'training'    => 'learn,guide,tips',
		'lesson'      => 'overview of,all about,importance of,types of,causes of,effects of,benefits of',
		'projectplan' => 'project,plan for',
		'marketing'   => 'marketing,brand,campaign',
		'launch'      => 'launch',
		'hiring'      => 'hiring,recruit',
		'event'       => 'event,conference',
		'market'      => 'market,competitor,industry',
		'demo'        => 'product,features,software,app ',
		'tech'        => 'technical,technology',
		'esg'         => 'sustainab',
		'onboarding'  => 'welcome',
	);
	foreach ( $extra as $k => $list ) if ( isset( $T[ $k ] ) ) $T[ $k ]['kw'] = array_merge( $T[ $k ]['kw'], explode( ',', $list ) );
	return $T;
}

function ds_dp_strong() {
	return array( 'pitch deck', 'investor pitch', 'investor deck', 'investor presentation', 'startup pitch', 'company overview', 'company profile', 'corporate profile', 'corporate presentation', 'company presentation', 'company introduction', 'about us', 'sales deck', 'sales presentation', 'sales pitch', 'business plan', 'case study', 'swot', 'town hall', 'all-hands', 'all hands', 'onboarding', 'business case', 'marketing plan', 'marketing strategy', 'project status', 'status update', 'status report', 'project plan', 'project kickoff', 'annual report', 'quarterly business review', 'qbr', 'product launch', 'product roadmap', 'sponsorship', 'portfolio', 'lessons learned', 'retrospective', 'post-mortem', 'competitor analysis', 'market analysis', 'market research', 'thesis', 'dissertation', 'research paper', 'training', 'workshop', 'webinar', 'keynote', 'proposal', 'product demo', 'self introduction', 'about me', 'appraisal', 'account review', 'franchise', 'financial results', 'budget presentation', 'strategic plan' );
}

function ds_dp_norm( $s ) {
	$s = function_exists( 'mb_strtolower' ) ? mb_strtolower( (string) $s, 'UTF-8' ) : strtolower( (string) $s );
	$s = preg_replace( '/[^\p{L}\p{N}&\-]+/u', ' ', $s );
	if ( $s === null ) return '';
	return ' ' . trim( preg_replace( '/\s+/', ' ', $s ) ) . ' ';
}

function ds_dp_detect( $text ) {
	$hay = ds_dp_norm( function_exists( 'mb_substr' ) ? mb_substr( (string) $text, 0, 700, 'UTF-8' ) : substr( (string) $text, 0, 700 ) );
	if ( trim( $hay ) === '' ) return array( 'auto', 0 );
	$strong = array_flip( ds_dp_strong() );
	$best = 'auto'; $bs = 0; $S = array(); $fk = ''; $fp = PHP_INT_MAX;
	foreach ( ds_dp_types() as $key => $t ) {
		$sc = 0; $seen = array();
		foreach ( $t['kw'] as $kw ) {
			$whole = ( $kw !== '' && substr( $kw, -1 ) === ' ' );
			$k = trim( ds_dp_norm( $kw ) );
			if ( $k === '' || isset( $seen[ $k ] ) ) continue;
			$needle = ' ' . $k . ( $whole ? ' ' : '' );
			$at = strpos( $hay, $needle );
			if ( $at === false ) continue;
			if ( isset( $strong[ $k ] ) && $at < $fp ) { $fp = $at; $fk = $key; }
			$seen[ $k ] = 1;
			$w = substr_count( $k, ' ' ) + 1;
			$pts = $w * $w * 10 + strlen( $k );
			if ( isset( $strong[ $k ] ) ) $pts *= 3;
			$sc += $pts;
		}
		if ( $sc > 0 ) $S[ $key ] = $sc;
	}
	if ( $fk !== '' && isset( $S[ $fk ] ) ) $S[ $fk ] += 200;
	foreach ( $S as $key => $sc ) if ( $sc > $bs ) { $bs = $sc; $best = $key; }
	if ( $bs < 12 ) return array( 'auto', $bs );
	return array( $best, $bs );
}

function ds_dp_plan( $key, $n ) {
	$T = ds_dp_types();
	$n = max( 1, (int) $n );
	$has_ag = $n >= 9; $has_cl = $n >= 4; /* an agenda only in longer decks */
	$m = max( 0, $n - 1 - ( $has_ag ? 1 : 0 ) - ( $has_cl ? 1 : 0 ) );
	$L = array(); $names = array();
	$mix = 'Use a content layout.';
	$mixdef = 'Where a slide says "Use a content layout", choose the type that fits that content best: "two_column" (two sides, before/after, problem/answer), "three_column" (three parallel items), "feature_grid" (4 items), "five_point" or "six_point" (5 or 6 parallel items), "bullet" (a short list). Do not use the same type on two slides in a row.';
	$mid = array();
	if ( $key !== 'auto' && isset( $T[ $key ] ) && $m > 0 ) {
		$sec = $T[ $key ]['sec']; $pool = array();
		foreach ( $sec as $i => $s ) {
			if ( $s['k'] === 'Q' && $m < 6 ) continue;
			$pool[] = array( $s['p'], $i );
		}
		usort( $pool, function ( $a, $b ) { return $a[0] === $b[0] ? $a[1] - $b[1] : $a[0] - $b[0]; } );
		$pick = array();
		foreach ( array_slice( $pool, 0, $m ) as $pp ) $pick[] = $pp[1];
		sort( $pick );
		foreach ( $pick as $i ) $mid[] = $sec[ $i ];
		$extra = $m - count( $mid );
		if ( $extra > 0 ) {
			$hosts = array();
			foreach ( $mid as $j => $s ) if ( $s['k'] === 'C' ) $hosts[] = $j;
			usort( $hosts, function ( $a, $b ) use ( $mid ) { return $mid[ $a ]['p'] === $mid[ $b ]['p'] ? $a - $b : $mid[ $a ]['p'] - $mid[ $b ]['p']; } );
			$add = array();
			for ( $e = 0; $e < $extra && $hosts; $e++ ) { $h = $hosts[ $e % count( $hosts ) ]; $add[ $h ] = ( isset( $add[ $h ] ) ? $add[ $h ] : 0 ) + 1; }
			$out = array();
			foreach ( $mid as $j => $s ) {
				$out[] = $s;
				if ( ! empty( $add[ $j ] ) ) for ( $x = 0; $x < $add[ $j ]; $x++ ) {
					$out[] = array( 'k' => 'C', 'p' => 9, 'n' => $s['n'] . ' in detail', 'g' => 'A deeper look at "' . $s['n'] . '": a further breakdown, example or supporting detail that the slide before did not cover', 'x' => 1 );
				}
			}
			$mid = $out;
		}
	}
	$k = 1;
	$L[] = 'Slide 1: type "title" - the presentation title and a one-line subtitle that states the promise of the deck.';
	if ( $has_ag ) { $k++; $L[] = '__AGENDA__'; }
	if ( $mid ) {
		foreach ( $mid as $s ) {
			$k++;
			if ( empty( $s['x'] ) ) $names[] = $s['n'];
			$head = 'Slide ' . $k . ': SECTION "' . $s['n'] . '" - ' . $s['g'] . '. ';
			if ( $s['k'] === 'S' ) $L[] = $head . 'Use type "stats" with 3 or 4 figures only if the topic or the business gives real numbers for this; otherwise use a content layout that explains it with concrete examples.';
			elseif ( $s['k'] === 'L' ) $L[] = $head . 'Use type "timeline" with 4 to 6 dated or ordered steps.';
			elseif ( $s['k'] === 'Q' ) $L[] = $head . 'Use type "quote" with one short testimonial and who said it (a customer or someone from the topic\'s own organisation; never invented words from a famous person).';
			else $L[] = $head . $mix;
		}
	} elseif ( $m > 0 ) {
		$a = $k + 1; $b = $k + $m; $k = $b;
		$r = 'Slide' . ( $m > 1 ? 's ' . $a . ' to ' . $b : ' ' . $a ) . ': content slides. First decide what kind of presentation the user is asking for (for example: investor pitch, company overview, sales presentation, product launch, marketing plan, business plan, proposal, business report, project update, strategy, training, lesson, research, case study, market analysis, event plan, comparison, process guide), then lay out the sections that kind of presentation normally has, in the order an experienced presenter would use. One section per slide, no repeated sections. ';
		if ( $m >= 3 ) $r .= 'Use one "stats" slide only if the topic gives real figures. ';
		if ( $m >= 4 ) $r .= 'Use a "timeline" only for a real sequence (a plan, history or steps in order). ';
		if ( $n >= 12 && $m >= 7 ) $r .= 'One may be type "quote". ';
		$L[] = $r . 'For the others: use a content layout.';
	}
	if ( $has_cl ) { $k++; $L[] = 'Slide ' . $k . ': type "closing" - a closing line, the single action wanted from the audience, and contact details.'; }
	foreach ( $L as $i => $ln ) if ( $ln === '__AGENDA__' ) {
		$L[ $i ] = 'Slide 2: type "agenda" - list the sections of this deck in the order they appear' . ( $names ? ' (' . implode( '; ', array_slice( $names, 0, 8 ) ) . ')' : '' ) . ', 4 to 7 items.';
	}
	$label = ( $key !== 'auto' && isset( $T[ $key ] ) ) ? $T[ $key ]['label'] : '';
	$top  = '=== SLIDE PLAN (follow exactly, ' . $n . ' slides) ===' . "\n";
	if ( $label !== '' ) $top .= 'This is a presentation of the kind: ' . $label . '. Follow the standard storyline for this kind of presentation, section by section, as listed. ';
	$top .= 'Write each slide title so it is specific to the user\'s topic (do not just copy the section name). Keep exactly ' . $n . ' slides in this order. If the user\'s request explicitly names the slides or sections they want, use the user\'s sections in place of the ones listed. ' . $mixdef . "\n";
	return $top . implode( "\n", $L ) . "\n";
}

function ds_dp_ctx( $set = null ) {
	static $c = null;
	if ( $set !== null ) $c = ( $set === false ? null : $set );
	return $c;
}

function ds_dp_arm( $result, $server, $req ) {
	try {
		if ( $result !== null || get_option( 'ds_dp_off' ) ) return $result;
		$route = (string) $req->get_route();
		if ( ! preg_match( '#^/ds/v1/(ai-preview|ai-confirm|generate-ppt)/?$#', $route ) ) return $result;
		$P = (array) $req->get_params();
		$text = ''; $keys = array();
		foreach ( $P as $pk => $pv ) {
			if ( ! is_string( $pv ) || strlen( $pv ) < 3 ) continue;
			$lk = strtolower( (string) $pk );
			if ( $lk === 'slide_type' || $lk === 'deck_type' ) continue;
			if ( ! preg_match( '/topic|prompt|instr|direct|brief|desc|title|subject|context|audience|purpose|goal|notes|outline|kind|category/', $lk ) ) continue;
			$keys[] = $pk;
			$text .= ' ' . $pv;
		}
		$forced = isset( $P['deck_type'] ) && is_string( $P['deck_type'] ) ? sanitize_key( $P['deck_type'] ) : '';
		$T = ds_dp_types();
		if ( $forced !== '' && ( $forced === 'auto' || isset( $T[ $forced ] ) ) ) $det = array( $forced, 999 );
		else $det = ds_dp_detect( $text );
		ds_dp_ctx( array( 'key' => $det[0], 'score' => $det[1], 'keys' => $keys, 'all' => array_keys( $P ), 'done' => 0, 'n' => 0, 'plan' => '' ) );
		add_filter( 'http_request_args', 'ds_dp_http', 10, 2 );
	} catch ( \Throwable $e ) { ds_dp_ctx( false ); }
	return $result;
}

function ds_dp_http( $args, $url ) {
	try {
		$c = ds_dp_ctx();
		if ( ! $c || strpos( (string) $url, '/chat/completions' ) === false ) return $args;
		if ( empty( $args['body'] ) || ! is_string( $args['body'] ) || strpos( $args['body'], 'SLIDE PLAN' ) === false ) return $args;
		$body = json_decode( $args['body'], true );
		if ( ! is_array( $body ) || empty( $body['messages'] ) || ! is_array( $body['messages'] ) ) return $args;
		$hit = false;
		foreach ( $body['messages'] as $i => $msg ) {
			if ( ! isset( $msg['content'] ) || ! is_string( $msg['content'] ) ) continue;
			if ( ! preg_match( '/=== SLIDE PLAN \(follow exactly, (\d+) slides\) ===\n(.*?)(?=\nReturn ONLY valid JSON)/s', $msg['content'], $mm, PREG_OFFSET_CAPTURE ) ) continue;
			$n = (int) $mm[1][0];
			if ( $n < 3 ) continue;
			$plan = ds_dp_plan( $c['key'], $n );
			if ( preg_match( '/^Storyline guidance:.*$/m', $mm[2][0], $sg ) ) $plan .= 'Tone and style note for this template: ' . trim( substr( $sg[0], 19 ) ) . "\n";
			$body['messages'][ $i ]['content'] = substr( $msg['content'], 0, $mm[0][1] ) . rtrim( $plan, "\n" ) . substr( $msg['content'], $mm[0][1] + strlen( $mm[0][0] ) );
			$c['done'] = 1; $c['n'] = $n; $c['plan'] = $plan; $hit = true;
		}
		if ( $hit ) {
			$enc = wp_json_encode( $body );
			if ( is_string( $enc ) && $enc !== '' ) { $args['body'] = $enc; ds_dp_ctx( $c ); }
		}
	} catch ( \Throwable $e ) {}
	return $args;
}

function ds_dp_done( $result, $server, $req ) {
	try {
		$c = ds_dp_ctx();
		if ( ! $c ) return $result;
		remove_filter( 'http_request_args', 'ds_dp_http', 10 );
		ds_dp_ctx( false );
		if ( $c['done'] && $result instanceof \WP_REST_Response ) {
			$d = $result->get_data();
			if ( is_array( $d ) ) {
				$T = ds_dp_types();
				$d['deck_type'] = $c['key'];
				$d['deck_type_label'] = isset( $T[ $c['key'] ] ) ? $T[ $c['key'] ]['label'] : 'General (AI chooses the storyline)';
				if ( function_exists( 'current_user_can' ) && current_user_can( 'manage_options' ) ) $d['deck_debug'] = array( 'score' => $c['score'], 'scanned' => $c['keys'], 'params' => $c['all'], 'n' => $c['n'], 'plan' => $c['plan'] );
				$result->set_data( $d );
			}
		}
	} catch ( \Throwable $e ) {}
	return $result;
}

add_filter( 'rest_pre_dispatch', 'ds_dp_arm', 4, 3 );
add_filter( 'rest_pre_dispatch', 'ds_dp_done', 6, 3 );
add_filter( 'rest_post_dispatch', function ( $r ) { if ( ds_dp_ctx() ) { remove_filter( 'http_request_args', 'ds_dp_http', 10 ); ds_dp_ctx( false ); } return $r; }, 99 );

add_action( 'rest_api_init', function () {
	register_rest_route( 'ds/v1', '/deck-type', array(
		'methods'             => 'POST',
		'permission_callback' => function () { return current_user_can( 'manage_options' ); },
		'callback'            => function ( $req ) {
			$T = ds_dp_types(); $out = array();
			$list = (array) $req->get_param( 'prompts' );
			$n = (int) $req->get_param( 'n' );
			foreach ( array_slice( $list, 0, 200 ) as $p ) {
				$d = ds_dp_detect( (string) $p );
				$row = array( 'prompt' => (string) $p, 'type' => $d[0], 'label' => isset( $T[ $d[0] ] ) ? $T[ $d[0] ]['label'] : 'General', 'score' => $d[1] );
				if ( $n > 0 ) $row['plan'] = ds_dp_plan( $d[0], $n );
				$out[] = $row;
			}
			$types = array();
			foreach ( $T as $k => $t ) $types[ $k ] = array( 'label' => $t['label'], 'sections' => count( $t['sec'] ) );
			return rest_ensure_response( array( 'results' => $out, 'types' => $types ) );
		},
	) );
} );
