export interface FirstLevel {
  // columns
  id: number
  name: string
  user_id: number
  // relations
  user: User
  second_level_models: SecondLevels
}
export type FirstLevels = FirstLevel[]
export type FirstLevelEditable = Pick<FirstLevel, 'name'> & {
  second_level_models: SecondLevelEditables
}
export type FirstLevelEditables = FirstLevelEditable[]
