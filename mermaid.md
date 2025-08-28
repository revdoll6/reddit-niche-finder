

This flowchart provides a comprehensive visualization of the optimal user journey through your Reddit Niche Finder app. Here's a breakdown of the key navigation paths:

## Main User Flows

1. **Search & Discovery Flow**
   - Users start at the Niche Explorer, enter keywords, and get results
   - They can filter/sort results to refine their search
   - From the results, they have three main options for each subreddit:
     - View detailed analysis
     - Add to an audience (with the "+" button)
     - Visit the actual subreddit

2. **Subreddit Detail Flow**
   - After clicking "View Details," users see a tabbed interface with:
     - Overview (key metrics)
     - Content Analysis (post types, topics)
     - User Activity (participation patterns)
     - Growth Trends (historical data)
   - From any tab, they can add the subreddit to an audience or return to results

3. **Audience Builder Flow**
   - As users add subreddits (with "+" buttons), they appear in the Audience Builder panel
   - Users can view the current audience composition
   - When ready, they name and save the audience
   - The saved audience is then available for detailed analysis

4. **Comparison Tool Flow**
   - Users select 2-5 subreddits for direct comparison
   - The comparison view shows metrics side-by-side
   - Users can analyze audience overlap and relative strengths/weaknesses
   - Comparisons can be saved for future reference

5. **Analytics Dashboard Flow**
   - Provides access to saved audiences
   - Shows broader trends across multiple subreddits
   - Offers insights into content performance and optimal posting times

This flowchart maintains your existing navigation structure while providing clear paths for users to follow as they explore, analyze, and leverage subreddit data for their marketing needs. The subgraphs organize related functionality to make the overall flow easier to understand.



flowchart TD
    %% Main entry points
    Login[Login/Register] --> Dashboard
    Dashboard --> NicheExplorer[Niche Explorer]
    
    %% Niche Explorer workflow
    subgraph SearchDiscovery["Search & Discovery"]
        NicheExplorer --> Search[Search Keywords]
        Search --> ResultsList[Search Results List]
        
        ResultsList --> FilterSort[Filter & Sort Results]
        FilterSort --> ResultsList
        
        ResultsList --> ExportCSV[Export Results to CSV]
        
        ResultsList --> SR1[Select Subreddit 1]
        ResultsList --> SR2[Select Subreddit 2]
        ResultsList --> SR3[Select Subreddit N]
    end
    
    %% Individual subreddit actions
    subgraph SubredditActions["Subreddit Actions"]
        SR1 --> |"View Details"| SubredditDetail
        SR2 --> |"View Details"| SubredditDetail
        SR3 --> |"View Details"| SubredditDetail
        
        SR1 --> |"+"| AddToAudience[Add to Audience Builder]
        SR2 --> |"+"| AddToAudience
        SR3 --> |"+"| AddToAudience
        
        SR1 --> |"Visit"| RedditSite[Open Reddit Site]
        SR2 --> |"Visit"| RedditSite
        SR3 --> |"Visit"| RedditSite
    end
    
    %% Subreddit details view
    subgraph SubredditDetailView["Subreddit Detail View"]
        SubredditDetail --> OverviewTab[Overview Tab]
        SubredditDetail --> ContentTab[Content Analysis Tab]
        SubredditDetail --> UserActivityTab[User Activity Tab]
        SubredditDetail --> GrowthTab[Growth Trends Tab]
        
        OverviewTab --> |"+"| AddToAudience
        ContentTab --> |"+"| AddToAudience
        UserActivityTab --> |"+"| AddToAudience
        GrowthTab --> |"+"| AddToAudience
        
        OverviewTab --> |"Back"| ResultsList
        ContentTab --> |"Back"| ResultsList
        UserActivityTab --> |"Back"| ResultsList
        GrowthTab --> |"Back"| ResultsList
    end
    
    %% Audience builder flow
    subgraph AudienceBuilder["Audience Builder"]
        AddToAudience --> AudiencePanel[Audience Builder Panel]
        AudiencePanel --> |"View Audience"| AudienceDetail[Audience Detail View]
        AudiencePanel --> |"Save Audience"| SaveAudience[Save Audience Dialog]
        AudiencePanel --> |"Clear All"| ClearAudience[Clear Audience]
        ClearAudience --> ResultsList
        
        SaveAudience --> |"Enter Name"| NameAudience[Name & Describe Audience]
        NameAudience --> SaveComplete[Save Complete]
        SaveComplete --> AudienceDetail
    end
    
    %% Audience analysis
    subgraph AudienceAnalysis["Audience Analysis"]
        AudienceDetail --> AudienceOverview[Audience Overview]
        AudienceDetail --> AudienceMetrics[Combined Metrics]
        AudienceDetail --> AudienceContent[Content Analysis]
        AudienceDetail --> AudienceMembers[Member Analysis]
        
        AudienceOverview --> |"Edit Audience"| EditAudience[Edit Audience]
        AudienceMetrics --> |"Edit Audience"| EditAudience
        AudienceContent --> |"Edit Audience"| EditAudience
        AudienceMembers --> |"Edit Audience"| EditAudience
        
        EditAudience --> AddToAudience
        
        AudienceOverview --> |"Export Report"| ExportReport[Export Report]
        AudienceMetrics --> |"Export Report"| ExportReport
        AudienceContent --> |"Export Report"| ExportReport
        AudienceMembers --> |"Export Report"| ExportReport
    end
    
    %% Comparison tool
    subgraph ComparisonTool["Comparison Tool"]
        SR1 --> |"Select for Comparison"| ComparisonSelection[Comparison Selection]
        SR2 --> |"Select for Comparison"| ComparisonSelection
        SR3 --> |"Select for Comparison"| ComparisonSelection
        
        ComparisonSelection --> |"Compare (2-5 subreddits)"| ComparisonView[Comparison View]
        ComparisonView --> ComparisonMetrics[Side-by-Side Metrics]
        ComparisonView --> OverlapAnalysis[Audience Overlap]
        ComparisonView --> StrengthsWeaknesses[Strengths & Weaknesses]
        
        ComparisonMetrics --> |"Save Comparison"| SaveComparison[Save Comparison]
        OverlapAnalysis --> |"Save Comparison"| SaveComparison
        StrengthsWeaknesses --> |"Save Comparison"| SaveComparison
        
        ComparisonMetrics --> |"Back"| ResultsList
        OverlapAnalysis --> |"Back"| ResultsList
        StrengthsWeaknesses --> |"Back"| ResultsList
    end
    
    %% Analytics section
    Dashboard --> AnalyticsDashboard[Analytics Dashboard]
    
    subgraph Analytics["Analytics"]
        AnalyticsDashboard --> SavedAudiences[Saved Audiences]
        AnalyticsDashboard --> EngagementTrends[Engagement Trends]
        AnalyticsDashboard --> ContentPerformance[Content Performance]
        AnalyticsDashboard --> PostingTimes[Best Posting Times]
        
        SavedAudiences --> AudienceDetail
    end
    
    %% Settings & Resources
    Dashboard --> Settings[Settings]
    Dashboard --> Resources[Resources]
    
    %% Style definitions
    classDef primary fill:#4169E1,color:white,stroke:#333,stroke-width:2px
    classDef secondary fill:#6495ED,color:white,stroke:#333
    classDef action fill:#90EE90,stroke:#333
    classDef view fill:#FFD700,stroke:#333
    classDef section fill:#8A2BE2,color:white,stroke:#333,stroke-width:2px
    
    %% Apply styles
    class Dashboard,NicheExplorer,AnalyticsDashboard,CompetitorAnalysis,ContentInsights,CommunityEngagement,SentimentAnalysis primary
    class Search,ResultsList,SubredditDetail,AudienceDetail,ComparisonView secondary
    class AddToAudience,SaveAudience,ClearAudience,ExportCSV,SaveComparison action
    class OverviewTab,ContentTab,UserActivityTab,GrowthTab,AudienceOverview,AudienceMetrics,AudienceContent,AudienceMembers view
    class SearchDiscovery,SubredditActions,SubredditDetailView,AudienceBuilder,AudienceAnalysis,ComparisonTool,Analytics section